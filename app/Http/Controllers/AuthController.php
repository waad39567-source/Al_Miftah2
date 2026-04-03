<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Mail\EmailVerification;
use App\Models\User;
use App\Services\AuthService;
use App\Services\FirebaseAuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @OA\Info(title="Al-Miftah Real Estate API", version="1.0.0",
 *     @OA\Contact(email="info@almiftah.com"))
 * @OA\Server(url="http://127.0.0.1:8000/api", description="Local Server")
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer", bearerFormat="JWT")
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService,
        private FirebaseAuthService $firebaseAuthService,
    ) {}

    public function register(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());
            return $this->successResponse(
                ['user' => new UserResource($result['user'])],
                'تم إنشاء الحساب بنجاح', 201
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء إنشاء الحساب', 500, null, $e->getMessage());
        }
    }

    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            if ($result === null)     return $this->errorResponse('بيانات الاعتماد غير صحيحة', 401);
            if ($result === false)    return $this->errorResponse('الحساب غير مفعل', 403);
            if ($result === 'banned') return $this->errorResponse('الحساب محظور. تواصل مع الدعم', 403);

            return $this->successResponse(
                ['user' => new UserResource($result['user']), 'token' => $result['token']],
                'تم تسجيل الدخول بنجاح'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الدخول', 500, null, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/firebase",
     *     summary="تسجيل الدخول عبر Firebase (Google / Phone / Email)",
     *     tags={"Authentication"},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"id_token"},
     *             @OA\Property(property="id_token", type="string", description="Firebase ID Token")
     *         )
     *     ),
     *     @OA\Response(response=200, description="تم تسجيل الدخول بنجاح"),
     *     @OA\Response(response=401, description="Firebase token غير صالح"),
     *     @OA\Response(response=403, description="الحساب محظور أو غير مفعل")
     * )
     */
    public function firebaseLogin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_token' => 'required|string',
                'phone'    => 'nullable|string|min:8|max:20',
            ]);

            $firebaseUser = $this->firebaseAuthService->verifyIdToken($request->id_token);

            if (!$firebaseUser) {
                return $this->errorResponse('Firebase token غير صالح', 401);
            }

            $result = $this->authService->loginOrRegisterWithFirebase(
                $firebaseUser,
                $this->firebaseAuthService,
                $request->phone
            );

            if ($result === 'needs_phone') {
                return $this->errorResponse('رقم الهاتف مطلوب لإكمال التسجيل', 422, ['needs_phone' => true]);
            }
            if ($result === false)    return $this->errorResponse('الحساب غير مفعل', 403);
            if ($result === 'banned') return $this->errorResponse('الحساب محظور. تواصل مع الدعم', 403);

            return $this->successResponse(
                ['user' => new UserResource($result['user']), 'token' => $result['token']],
                'تم تسجيل الدخول بنجاح'
            );
        } catch (Throwable $e) {
            if ($e instanceof ValidationException) throw $e;
            Log::error('Firebase login error: ' . $e->getMessage());
            return $this->errorResponse('Firebase Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/firebase/set-password",
     *     summary="تعيين كلمة مرور لمستخدمي Firebase (Google/Phone)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"id_token","password","password_confirmation"},
     *             @OA\Property(property="id_token", type="string"),
     *             @OA\Property(property="password", type="string", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="تم تعيين كلمة المرور"),
     *     @OA\Response(response=401, description="Firebase token غير صالح"),
     *     @OA\Response(response=403, description="المستخدم غير مرتبط بـ Firebase")
     * )
     */
    public function setFirebasePassword(AuthRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isFirebaseUser()) {
                return $this->errorResponse('هذا الإجراء متاح فقط لمستخدمي Firebase', 403);
            }

            $firebaseUser = $this->firebaseAuthService->verifyIdToken($request->id_token);

            if (!$firebaseUser || $firebaseUser['localId'] !== $user->firebase_uid) {
                return $this->errorResponse('Firebase token غير صالح أو لا يطابق الحساب', 401);
            }

            $this->authService->setFirebasePassword($user, $request->password);

            return $this->successResponse(null, 'تم تعيين كلمة المرور بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تعيين كلمة المرور', 500, null, $e->getMessage());
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

    public function updateProfile(AuthRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->update($request->validated());
            return $this->successResponse(new UserResource($user->fresh()), 'تم تحديث الملف الشخصي بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث الملف الشخصي', 500, null, $e->getMessage());
        }
    }

    public function changePassword(AuthRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->changePassword($request->user(), $request->validated());
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
                return $this->errorResponse('ليس لديك صلاحية لهذه العملية', 403);
            }
            $user = $this->authService->promoteToAdmin($request->validated());
            if (!$user) return $this->errorResponse('المستخدم غير موجود', 404);
            return $this->successResponse(new UserResource($user), 'تم ترقية المستخدم إلى مشرف بنجاح');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ', 500, null, $e->getMessage());
        }
    }

    public function createUser(AuthRequest $request): JsonResponse
    {
        try {
            if (!$request->user()->isAdmin()) {
                return $this->errorResponse('ليس لديك صلاحية لهذه العملية', 403);
            }
            $user = $this->authService->createUser($request->validated());
            return $this->successResponse(new UserResource($user), 'تم إنشاء المستخدم بنجاح', 201);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء إنشاء المستخدم', 500, null, $e->getMessage());
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            $email = $request->email;
            if ($request->has('token')) {
                $decoded = base64_decode($request->token);
                $parts = explode('|', $decoded);
                if (count($parts) >= 1) $email = $parts[0];
            }
            $request->merge(['email' => $email]);
            $request->validate(['email' => 'required|email|exists:users,email']);

            $user = User::where('email', $email)->first();

            if (!is_null($user->email_verified_at)) {
                if ($request->isMethod('get')) return view('emails.verification-success', ['alreadyVerified' => true]);
                return $this->errorResponse('البريد الإلكتروني موثق مسبقاً', 400);
            }

            $user->update(['email_verified_at' => now()]);

            if ($request->isMethod('get')) return view('emails.verification-success', ['alreadyVerified' => false]);
            return $this->successResponse(['email_verified_at' => $user->email_verified_at], 'تم توثيق البريد الإلكتروني بنجاح');
        } catch (Throwable $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            if ($request->isMethod('get')) return view('emails.verification-success', ['error' => true, 'message' => 'حدث خطأ']);
            return $this->errorResponse('حدث خطأ أثناء توثيق البريد الإلكتروني', 500, null, $e->getMessage());
        }
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email|exists:users,email']);
            $user = User::where('email', $request->email)->first();
            Mail::to($user->email)->send(new EmailVerification($user));
            return $this->successResponse(null, 'تم إرسال رابط التوثيق بنجاح');
        } catch (Throwable $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء إرسال التوثيق', 500, null, $e->getMessage());
        }
    }
}
