<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService
    ) {}

    public function register(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->successResponse([
                'user' => new UserResource($result['user']),
            ], 'تم التسجيل بنجاح. لم يتم توثيق حسابك بعد يرجى التحقق من بريدك الالكتروني لتوثيق الحساب', 201);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء التسجيل', 500, null, $e->getMessage());
        }
    }

    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            if ($result === null) {
                return $this->errorResponse('بيانات الاعتماد غير صحيحة', 401);
            }

            if ($result === false) {
                return $this->errorResponse('الحساب غير نشط', 403);
            }

            if ($result === 'unverified') {
                return $this->errorResponse('لم يتم توثيق حسابك بعد يرجى التحقق من بريدك الالكتروني لتوثيق الحساب', 403);
            }

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 'تم تسجيل الدخول بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الدخول', 500, null, $e->getMessage());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الخروج', 500, null, $e->getMessage());
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(new UserResource($request->user()));
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ', 500, null, $e->getMessage());
        }
    }

    public function changePassword(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->changePassword(
                $request->user(),
                $request->validated()
            );

            if (!$result) {
                return $this->errorResponse('كلمة المرور الحالية غير صحيحة', 400);
            }

            return $this->successResponse(null, 'تم تغيير كلمة المرور بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تغيير كلمة المرور', 500, null, $e->getMessage());
        }
    }

    public function promoteToAdmin(AuthRequest $request): JsonResponse
    {
        try {
            if (!$request->user()->isAdmin()) {
                return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
            }

            $user = $this->authService->promoteToAdmin($request->validated());

            if (!$user) {
                return $this->errorResponse('المستخدم غير موجود', 404);
            }

            return $this->successResponse(
                new UserResource($user),
                'تم ترقية المستخدم إلى مسؤول بنجاح'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء الترقية', 500, null, $e->getMessage());
        }
    }

    public function createUser(AuthRequest $request): JsonResponse
    {
        try {
            if (!$request->user()->isAdmin()) {
                return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
            }

            $user = $this->authService->createUser($request->validated());

            return $this->successResponse(
                new UserResource($user),
                'تم إنشاء المستخدم بنجاح',
                201
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء إنشاء المستخدم', 500, null, $e->getMessage());
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!is_null($user->email_verified_at)) {
                return $this->errorResponse('تم توثيق البريد الإلكتروني مسبقاً', 400);
            }

            $user->update(['email_verified_at' => now()]);

            return $this->successResponse([
                'email_verified_at' => $user->email_verified_at,
            ], 'تم توثيق البريد الإلكتروني بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء توثيق البريد الإلكتروني', 500, null, $e->getMessage());
        }
    }
}
