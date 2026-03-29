<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Services\AccountService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Throwable;

class AccountController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AccountService $accountService
    ) {}

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $this->accountService->deleteAccount(
                $user,
                $request->validated('password')
            );

            if (!$result) {
                return $this->errorResponse('كلمة المرور غير صحيحة', 401);
            }

            return $this->successResponse(null, 'تم حذف الحساب وجميع البيانات المرتبطة بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف الحساب', 500, null, $e->getMessage());
        }
    }

    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $this->accountService->updateEmail(
                $user,
                $request->validated('email'),
                $request->validated('password')
            );

            if (!$result) {
                return $this->errorResponse('كلمة المرور غير صحيحة أو البريد مستخدم مسبقاً', 401);
            }

            return $this->successResponse(null, 'تم تغيير البريد الإلكتروني بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تغيير البريد', 500, null, $e->getMessage());
        }
    }
}
