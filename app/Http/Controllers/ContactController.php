<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\ContactRequest;
use App\Services\ContactService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContactController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ContactService $contactService
    ) {}

    public function store(StoreContactRequest $request): JsonResponse
    {
        if (!Gate::allows('create', ContactRequest::class)) {
            return $this->errorResponse('غير مصرح لك بإرسال طلب تواصل', 403);
        }

        try {
            $contactRequest = $this->contactService->create(
                $request->validated(),
                $request->user()->id
            );

            return $this->successResponse(
                new ContactResource($contactRequest),
                'تم إرسال طلب التواصل بنجاح',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function myRequests(Request $request): JsonResponse
    {
        if (!Gate::allows('viewMyRequests', ContactRequest::class)) {
            return $this->errorResponse('غير مصرح لك بعرض طلباتك', 403);
        }

        $filters = array_merge($request->only(['status', 'per_page']), [
            'user_id' => $request->user()->id
        ]);

        $requests = $this->contactService->getAll($filters);

        return $this->successResponse(ContactResource::collection($requests));
    }

    public function myReceivedRequests(Request $request): JsonResponse
    {
        if (!Gate::allows('viewMyReceived', ContactRequest::class)) {
            return $this->errorResponse('غير مصرح لك بعرض الطلبات المستلمة', 403);
        }

        $filters = array_merge($request->only(['status', 'per_page']), [
            'owner_id' => $request->user()->id
        ]);

        $requests = $this->contactService->getAll($filters);

        return $this->successResponse(ContactResource::collection($requests));
    }

    public function checkStatus(Request $request, int $propertyId): JsonResponse
    {
        $contactRequest = $this->contactService->getUserRequestForProperty(
            $request->user()->id,
            $propertyId
        );

        if (!$contactRequest) {
            return $this->successResponse([
                'has_request' => false,
                'status' => null
            ]);
        }

        $response = [
            'has_request' => true,
            'status' => $contactRequest->status,
            'created_at' => $contactRequest->created_at->toDateTimeString(),
        ];

        if ($contactRequest->status === 'approved' && $contactRequest->owner) {
            $response['owner'] = [
                'name' => $contactRequest->owner->name,
                'phone' => $contactRequest->owner->phone,
            ];
        }

        return $this->successResponse($response);
    }
}
