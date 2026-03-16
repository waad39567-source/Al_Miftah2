<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertySimpleResource;
use App\Models\Property;
use App\Services\PropertyService;
use App\Services\FirebaseService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Properties",
 *     description="إدارة العقارات"
 * )
 */
class PropertyController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private PropertyService $propertyService,
        private FirebaseService $firebaseService
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/properties",
     *     summary="جلب قائمة العقارات المنشورة",
     *     tags={"Properties"},
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"sale", "rent"})),
     *     @OA\Parameter(name="property_type", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="region_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="min_area", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_area", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="نجاح", @OA\JsonContent(type="object"))
     * )
     */
    public function index(Request $request)
    {
        if ($request->user() && !Gate::allows('viewAny', Property::class)) {
            return $this->errorResponse('غير مصرح لك بعرض العقارات', 403);
        }

        $filters = $request->only([
            'type', 'property_type', 'region_id', 'region_ids',
            'status', 'search', 'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]);

        if (!isset($filters['status'])) {
            $filters['status'] = 'active';
        }

        $properties = $this->propertyService->getAll($filters);

        return $this->successResponse([
            'data' => PropertySimpleResource::collection($properties),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/properties/search",
     *     summary="البحث عن العقارات",
     *     tags={"Properties"},
     *     @OA\Response(response=200, description="نجاح")
     * )
     */
    public function search(Request $request)
    {
        if ($request->user() && !Gate::allows('viewAny', Property::class)) {
            return $this->errorResponse('غير مصرح لك بالبحث', 403);
        }

        $filters = $request->only([
            'type', 'property_type', 'region_id', 'region_ids',
            'status', 'search', 'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]);

        $properties = $this->propertyService->search($filters);

        return $this->successResponse($properties);
    }

    /**
     * @OA\Get(
     *     path="/properties/advanced-search",
     *     summary="البحث المتقدم عن العقارات",
     *     tags={"Properties"},
     *     @OA\Parameter(name="governorate_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="city_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="neighborhood_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="region_ids", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="نجاح")
     * )
     */
    public function advancedSearch(Request $request)
    {
        if ($request->user() && !Gate::allows('viewAny', Property::class)) {
            return $this->errorResponse('غير مصرح لك بالبحث', 403);
        }

        $filters = $request->only([
            'type', 'property_type', 
            'governorate_id', 'city_id', 'neighborhood_id',
            'region_ids', 'region_names',
            'status', 'search', 'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]);

        $properties = $this->propertyService->advancedSearch($filters);

        return $this->successResponse($properties);
    }

    /**
     * @OA\Post(
     *     path="/properties",
     *     summary="إنشاء عقار جديد",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "price", "type", "property_type", "area", "region_id", "location"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="type", type="string", enum={"sale", "rent"}),
     *                 @OA\Property(property="property_type", type="string"),
     *                 @OA\Property(property="area", type="number"),
     *                 @OA\Property(property="region_id", type="integer"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="latitude", type="number", nullable=true),
     *                 @OA\Property(property="longitude", type="number", nullable=true),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="تم الإنشاء"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=422, description="خطأ في التحقق")
     * )
     */
    public function store(PropertyRequest $request)
    {
        if (!Gate::allows('create', Property::class)) {
            return $this->errorResponse('غير مصرح لك بإنشاء عقار', 403);
        }

        $property = $this->propertyService->create($request->validated(), $request->user()->id);

        if ($request->hasFile('images')) {
            $this->propertyService->addImages($property, $request->file('images'));
        }

        if ($request->has('images_base64')) {
            $base64Images = $request->input('images_base64');
            if (is_array($base64Images)) {
                $this->propertyService->addImagesFromBase64($property, $base64Images);
            }
        }

        // إرسال إشعار للأدمن عند إضافة عقار جديد
        try {
            $this->firebaseService->sendToAdmins(
                'عقار جديد',
                'تمت إضافة عقار جديد: ' . $property->title,
                ['type' => 'new_property', 'id' => (string) $property->id]
            );
        } catch (\Exception $e) {
            // تجاهل خطأ الإشعار
        }

        return $this->successResponse(
            new PropertyResource($property->load(['owner', 'region', 'images'])),
            'تم إنشاء العقار بنجاح',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/properties/{id}",
     *     summary="جلب تفاصيل عقار",
     *     tags={"Properties"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="نجاح"),
     *     @OA\Response(response=404, description="العقار غير موجود")
     * )
     */
    public function show(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if ($request->user() && !Gate::allows('view', $property)) {
            return $this->errorResponse('غير مصرح لك بعرض هذا العقار', 403);
        }

        $contactInfo = null;
        if ($request->user()) {
            $contactRequest = $this->propertyService->getUserContactRequest(
                $request->user()->id,
                $id
            );

            if ($contactRequest && $contactRequest->status === 'approved') {
                $contactInfo = [
                    'owner_name' => $property->owner->name,
                    'owner_phone' => $property->owner->phone,
                ];
            }
        }

        return $this->successResponse([
            'property' => new PropertyResource($property),
            'contact_info' => $contactInfo
        ]);
    }

    /**
     * @OA\Put(
     *     path="/properties/{id}",
     *     summary="تحديث عقار",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="type", type="string", enum={"sale", "rent"}),
     *                 @OA\Property(property="property_type", type="string"),
     *                 @OA\Property(property="area", type="number"),
     *                 @OA\Property(property="region_id", type="integer"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="تم التحديث"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=404, description="غير موجود")
     * )
     */
    public function update(PropertyRequest $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('update', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لتعديل هذا العقار', 403);
        }

        $this->propertyService->update($property, $request->validated());

        if ($request->hasFile('images')) {
            $this->propertyService->addImages($property, $request->file('images'));
        }

        if ($request->has('images_base64')) {
            $base64Images = $request->input('images_base64');
            if (is_array($base64Images)) {
                $this->propertyService->addImagesFromBase64($property, $base64Images);
            }
        }

        return $this->successResponse(
            new PropertyResource($property->fresh(['owner', 'region', 'images'])),
            'تم تحديث العقار بنجاح'
        );
    }

    /**
     * @OA\Delete(
     *     path="/properties/{id}",
     *     summary="حذف عقار",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="تم الحذف"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=404, description="غير موجود")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('delete', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لحذف هذا العقار', 403);
        }

        $this->propertyService->delete($property);

        return $this->successResponse(null, 'تم حذف العقار بنجاح');
    }

    /**
     * @OA\Get(
     *     path="/properties/my-properties",
     *     summary="جلب عقارات المستخدم الحالي",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending", "active", "rented", "sold", "rejected"})),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="نجاح")
     * )
     */
    public function myProperties(Request $request)
    {
        $filters = array_merge($request->only([
            'type', 'region_id', 'status', 'search', 
            'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]), [
            'owner_id' => $request->user()->id
        ]);

        $properties = $this->propertyService->getAllForAdmin($filters);

        return $this->successResponse([
            'data' => PropertySimpleResource::collection($properties),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/properties/{id}/images/{imageId}",
     *     summary="حذف صورة من عقار",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="imageId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="تم الحذف"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=404, description="الصورة غير موجودة")
     * )
     */
    public function deleteImage(Request $request, $id, $imageId)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('deleteImage', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لحذف صورة هذا العقار', 403);
        }

        $deleted = $this->propertyService->deleteImage($property, $imageId);

        if (!$deleted) {
            return $this->errorResponse('الصورة غير موجودة', 404);
        }

        return $this->successResponse(null, 'تم حذف الصورة بنجاح');
    }

    /**
     * @OA\Post(
     *     path="/properties/{id}/rented",
     *     summary="تعليم العقار كمؤجر",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="تم"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=404, description="غير موجود")
     * )
     */
    public function markAsRented(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('markAsRented', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لتعديل هذا العقار', 403);
        }

        $this->propertyService->markAsRented($property);

        return $this->successResponse(null, 'تم تعليم العقار كمؤجر');
    }

    /**
     * @OA\Post(
     *     path="/properties/{id}/sold",
     *     summary="تعليم العقار كمباع",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="تم"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=404, description="غير موجود")
     * )
     */
    public function markAsSold(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('markAsSold', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لتعديل هذا العقار', 403);
        }

        $this->propertyService->markAsSold($property);

        return $this->successResponse(null, 'تم تعليم العقار كمباع');
    }
}
