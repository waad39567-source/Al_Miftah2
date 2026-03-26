<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminPropertyRequest;
use App\Http\Requests\DashboardRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\RecentActivityPropertyResource;
use App\Http\Resources\RecentActivityContactResource;
use App\Http\Resources\RecentActivityUserResource;
use App\Models\Property;
use App\Models\Region;
use App\Models\ContactRequest;
use App\Models\User;
use App\Services\AdminService;
use App\Services\FirebaseService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="إدارة الأدمن"
 * )
 */
class AdminController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AdminService $adminService,
        private FirebaseService $firebaseService
    ) {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isAdmin()) {
                return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
            }
            return $next($request);
        });
    }
    
    public function getUsers(Request $request)
    {
        if (!Gate::allows('viewAny', User::class)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $filters = $request->only(['search', 'per_page', 'role', 'is_verified', 'is_banned', 'is_active', 'sort_by', 'sort_order']);
        $users = $this->adminService->getUsers($filters);

        return $this->successResponse($users);
    }

    public function getUnverifiedUsers(Request $request)
    {
        if (!Gate::allows('viewAny', User::class)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $filters = $request->only(['search', 'per_page', 'sort_by', 'sort_order']);
        $users = $this->adminService->getUnverifiedUsers($filters);

        return $this->successResponse($users);
    }

    public function verifyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('المستخدم غير موجود', 404);
        }

        if (!Gate::allows('verifyUser', $user)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        if ($user->email_verified_at) {
            return $this->errorResponse('المستخدم موثق مسبقاً', 400);
        }

        $user = $this->adminService->verifyUser($user);

        return $this->successResponse(new UserResource($user), 'تم توثيق المستخدم بنجاح');
    }

    public function banUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('المستخدم غير موجود', 404);
        }

        if (!Gate::allows('banUser', $user)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        if ($user->is_banned) {
            return $this->errorResponse('المستخدم محظور مسبقاً', 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $reason = $request->input('reason');
        $user = $this->adminService->banUser($user, $reason);

        return $this->successResponse(new UserResource($user), 'تم حظر المستخدم بنجاح');
    }

    public function unbanUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('المستخدم غير موجود', 404);
        }

        if (!Gate::allows('unbanUser', $user)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        if (!$user->is_banned) {
            return $this->errorResponse('المستخدم غير محظور', 400);
        }

        $user = $this->adminService->unbanUser($user);

        return $this->successResponse(new UserResource($user), 'تم إلغاء حظر المستخدم بنجاح');
    }

    public function toggleUserActive($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('المستخدم غير موجود', 404);
        }

        if (!Gate::allows('toggleUserActive', $user)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $user = $this->adminService->toggleUserActive($user);

        // إرسال إشعار للمستخدم عند التفعيل
        if ($user->is_active && $user->role === 'owner') {
            $this->firebaseService->sendToUser(
                $user->id,
                'تم تفعيل حسابك',
                'تم تفعيل حساب مكتبكم العقاري بنجاح. يمكنكم الآن إضافة العقارات.',
                ['type' => 'account_activated']
            );
        }

        $message = $user->is_active ? 'تم تفعيل المستخدم بنجاح' : 'تم إلغاء تفعيل المستخدم بنجاح';

        return $this->successResponse(new UserResource($user), $message);
    }

    public function getProperties(Request $request)
    {
        if (!Gate::allows('viewAnyForAdmin', Property::class)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $filters = $request->only(['type', 'region_id', 'status', 'search', 'per_page', 'sort_by', 'sort_order']);
        $properties = $this->adminService->getProperties($filters);

        return $this->successResponse($properties);
    }

    public function approveProperty(Request $request, $id)
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('approve', $property)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $this->adminService->approveProperty($property, $request->user()->id);

        $this->firebaseService->sendToUser(
            $property->owner_id,
            'تمت الموافقة على عقاركم',
            'تمت الموافقة على عقار: ' . $property->title,
            ['type' => 'property_approved', 'id' => (string) $property->id]
        );

        return $this->successResponse(
            new PropertyResource($property->fresh()),
            'تم الموافقة على العقار بنجاح'
        );
    }

    public function rejectProperty(AdminPropertyRequest $request, $id)
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('reject', $property)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $this->adminService->rejectProperty($property, $request->user()->id, $request->reason);

        $this->firebaseService->sendToUser(
            $property->owner_id,
            'تم رفض عقاركم',
            'تم رفض عقار: ' . $property->title . '. السبب: ' . ($request->reason ?? ''),
            ['type' => 'property_rejected', 'id' => (string) $property->id]
        );

        return $this->successResponse(
            new PropertyResource($property->fresh()),
            'تم رفض العقار بنجاح'
        );
    }

    public function getContactRequests(Request $request)
    {
        if (!Gate::allows('viewAnyForAdmin', ContactRequest::class)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $filters = $request->only(['status', 'per_page', 'sort_by', 'sort_order']);
        $requests = $this->adminService->getContactRequests($filters);

        return $this->successResponse($requests);
    }

    public function approveContactRequest(Request $request, $id)
    {
        $contactRequest = ContactRequest::find($id);
        if (!$contactRequest) {
            return $this->errorResponse('طلب التواصل غير موجود', 404);
        }

        if (!Gate::allows('approve', $contactRequest)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        if ($contactRequest->status !== 'pending') {
            return $this->errorResponse('تم معالجة هذا الطلب مسبقاً', 400);
        }

        $this->adminService->approveContactRequest($contactRequest, $request->user()->id);

        // إرسال إشعار للمستخدم بالموافقة مع رقم المكتب
        $owner = $contactRequest->owner;
        $this->firebaseService->sendToUser(
            $contactRequest->user_id,
            'تم الموافقة على طلبك',
            'تمت الموافقة على طلبكم. رقم المكتب: ' . $owner->phone,
            ['type' => 'contact_approved', 'phone' => $owner->phone]
        );

        return $this->successResponse(
            new ContactResource($contactRequest->fresh(['property', 'user', 'owner'])),
            'تم الموافقة على طلب التواصل بنجاح'
        );
    }

    public function rejectContactRequest(AdminPropertyRequest $request, $id)
    {
        $contactRequest = ContactRequest::find($id);
        if (!$contactRequest) {
            return $this->errorResponse('طلب التواصل غير موجود', 404);
        }

        if (!Gate::allows('reject', $contactRequest)) {
            return $this->errorResponse('غير مصرح', 403);
        }

        if ($contactRequest->status !== 'pending') {
            return $this->errorResponse('تم معالجة هذا الطلب مسبقاً', 400);
        }

        $this->adminService->rejectContactRequest($contactRequest, $request->user()->id, $request->reason);

        // إرسال إشعار للمستخدم بالرفض
        $this->firebaseService->sendToUser(
            $contactRequest->user_id,
            'تم رفض طلبك',
            'تم رفض طلب التواصل. السبب: ' . ($request->reason ?? 'لا يوجد سبب محدد'),
            ['type' => 'contact_rejected']
        );

        return $this->successResponse(
            new ContactResource($contactRequest->fresh(['property', 'user', 'owner'])),
            'تم رفض طلب التواصل بنجاح'
        );
    }

    public function createRegion(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:country,governorate,city,neighborhood',
            'parent_id' => 'nullable|exists:regions,id',
        ]);

        $region = Region::create($request->all());

        return $this->successResponse($region, 'تم إنشاء المنطقة بنجاح', 201);
    }

    public function updateRegion(Request $request, $id)
    {
        $region = Region::find($id);
        if (!$region) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:country,governorate,city,neighborhood',
            'parent_id' => 'nullable|exists:regions,id',
        ]);

        $region->update($request->all());

        return $this->successResponse($region, 'تم تحديث المنطقة بنجاح');
    }

    public function deleteRegion($id)
    {
        $region = Region::find($id);
        if (!$region) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        $region->delete();

        return $this->successResponse(null, 'تم حذف المنطقة بنجاح');
    }

    public function statistics()
    {
        $statistics = $this->adminService->getStatistics();

        return $this->successResponse($statistics);
    }

    public function recentActivities(DashboardRequest $request)
    {
        $limit = $request->input('limit', 10);
        $activities = $this->adminService->getRecentActivities($limit);

        return $this->successResponse([
            'properties' => RecentActivityPropertyResource::collection(collect($activities['properties'])),
            'contact_requests' => RecentActivityContactResource::collection(collect($activities['contact_requests'])),
            'users' => RecentActivityUserResource::collection(collect($activities['users'])),
        ]);
    }

    public function chartData()
    {
        $data = $this->adminService->getChartData();

        return $this->successResponse($data);
    }

    public function propertiesByRegion()
    {
        $data = $this->adminService->getPropertiesByRegion();

        return $this->successResponse($data);
    }

    public function propertiesByType()
    {
        $data = $this->adminService->getPropertiesByType();

        return $this->successResponse($data);
    }


    public function propertiesSummary(Request $request)
    {
        try {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            $type = $request->input('type', 'all');

            $data = $this->adminService->getPropertiesSummary($fromDate, $toDate, $type);

            return $this->successResponse($data);
        } catch (\Exception $e) {
            Log::error('propertiesSummary error: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ', 500, null, $e->getMessage());
        }
    }

    public function usersRegistration(Request $request)
    {
        $period = $request->input('period', 'all');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $data = $this->adminService->getUsersRegistration($period, $fromDate, $toDate);

        return $this->successResponse($data);
    }

    public function topActiveRegions(Request $request)
    {
        $limit = $request->input('limit', 10);

        $data = $this->adminService->getTopActiveRegions($limit);

        return $this->successResponse($data);
    }
}
