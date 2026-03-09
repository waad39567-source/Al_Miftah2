<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendVerificationEmailJob;
use App\Mail\EmailVerification;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * @OA\Info(
 *     title="شركة المفتاح API",
 *     version="1.0.0",
 *     description="API للعقارات ونظام المستخدمين",
 *     @OA\Contact(
 *         email="info@almiftah.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api",
 *     description="Local Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="تسجيل مستخدم جديد",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "phone"},
     *             @OA\Property(property="name", type="string", example="اسم المستخدم"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="966501234567"),
     *             @OA\Property(property="role", type="string", example="user", enum={"user", "owner"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="تم التسجيل بنجاح"),
     *     @OA\Response(response=422, description="خطأ في التحقق")
     * )
     */
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

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!is_null($user->email_verified_at)) {
                return $this->errorResponse('تم توثيق البريد الإلكتروني مسبقاً', 400);
            }

            SendVerificationEmailJob::dispatch($user);

            return $this->successResponse(null, 'تم إضافة إرسال رابط توثيق البريد الإلكتروني إلى قائمة الانتظار');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء إضافة البريد الإلكتروني إلى قائمة الانتظار', 500, null, $e->getMessage());
        }
    }
}
