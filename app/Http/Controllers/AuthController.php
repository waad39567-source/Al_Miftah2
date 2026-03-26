<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Mail\EmailVerification;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="المصادقة والتسجيل"
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

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="تسجيل الدخول",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="نجاح", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="بيانات غير صحيحة"),
     *     @OA\Response(response=403, description="غير نشط أو غير موثق")
     * )
     */
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

            if ($result === 'banned') {
                return $this->errorResponse('الحساب محظور. يرجى التواصل مع الإدارة', 403);
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

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="تسجيل الخروج",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="نجاح")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الخروج', 500, null, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="جلب بيانات المستخدم الحالي",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="نجاح")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(new UserResource($request->user()));
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ', 500, null, $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/auth/profile",
     *     summary="تعديل بيانات الملف الشخصي",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="اسم المستخدم"),
     *             @OA\Property(property="phone", type="string", example="966501234567")
     *         )
     *     ),
     *     @OA\Response(response=200, description="نجاح"),
     *     @OA\Response(response=422, description="خطأ في التحقق")
     * )
     */
    public function updateProfile(AuthRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $user->update($validated);

            return $this->successResponse(
                new UserResource($user->fresh()),
                'تم تحديث الملف الشخصي بنجاح'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث الملف الشخصي', 500, null, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/change-password",
     *     summary="تغيير كلمة المرور",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="نجاح"),
     *     @OA\Response(response=400, description="كلمة المرور الحالية خطأ")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/auth/promote-to-admin",
     *     summary="ترقية مستخدم لأدمن",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=5),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="نجاح"),
     *     @OA\Response(response=403, description="غير مصرح"),
     *     @OA\Response(response=404, description="المستخدم غير موجود")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/admin/users/create",
     *     summary="إنشاء مستخدم جديد (أدمن)",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "role"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="role", type="string", enum={"user", "owner", "admin"}),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="تم الإنشاء"),
     *     @OA\Response(response=403, description="غير مصرح")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/auth/verify-email",
     *     summary="توثيق البريد الإلكتروني",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="نجاح"),
     *     @OA\Response(response=400, description="موثق مسبقاً")
     * )
     */
    public function verifyEmail(Request $request)
    {
        try {
            $email = $request->email;

            if ($request->has('token')) {
                $decoded = base64_decode($request->token);
                $parts = explode('|', $decoded);
                if (count($parts) >= 1) {
                    $email = $parts[0];
                }
            }

            $request->merge(['email' => $email]);
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $email)->first();

            if (!is_null($user->email_verified_at)) {
                if ($request->isMethod('get')) {
                    return view('emails.verification-success', [
                        'alreadyVerified' => true
                    ]);
                }
                return $this->errorResponse('تم توثيق البريد الإلكتروني مسبقاً', 400);
            }

            $user->update(['email_verified_at' => now()]);
            Log::info('Email verified successfully', ['email' => $email]);

            if ($request->isMethod('get')) {
                return view('emails.verification-success', [
                    'alreadyVerified' => false
                ]);
            }

            return $this->successResponse([
                'email_verified_at' => $user->email_verified_at,
            ], 'تم توثيق البريد الإلكتروني بنجاح');
        } catch (Throwable $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            if ($request->isMethod('get')) {
                return view('emails.verification-success', [
                    'error' => true,
                    'message' => 'حدث خطأ أثناء توثيق البريد الإلكتروني'
                ]);
            }
            return $this->errorResponse('حدث خطأ أثناء توثيق البريد الإلكتروني', 500, null, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/send-verification-email",
     *     summary="إرسال رابط توثيق البريد",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="نجاح"),
     *     @OA\Response(response=400, description="موثق مسبقاً")
     * )
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            Mail::to($user->email)->send(new EmailVerification($user));

            Log::info('Verification email sent successfully', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

            return $this->successResponse(null, 'تم إرسال رابط توثيق البريد الإلكتروني');
        } catch (Throwable $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء إرسال البريد الإلكتروني', 500, null, $e->getMessage());
        }
    }
}
