<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Services\ContactService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ContactService $contactService
    ) {}

    public function store(ContactRequest $request): JsonResponse
    {
        $contactRequest = $this->contactService->create($request->validated());

        return $this->successResponse(
            new ContactResource($contactRequest),
            'تم إرسال طلب التواصل بنجاح',
            201
        );
    }
}
