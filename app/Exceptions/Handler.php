<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e);
            }
        });
    }


    private function handleApiException(Throwable $e)
    {
        $statusCode = 500;
        $message = 'حدث خطأ داخلي في الخادم';
        $errors = null;


        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = 'المورد المطلوب غير موجود.';
        } elseif ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'غير مصرح لك بالوصول، يرجى تسجيل الدخول';
        } elseif ($e instanceof AccessDeniedHttpException) {
            $statusCode = 403;
            $message =  'لا تملك الصلاحيات الكافية لتنفيذ هذا الإجراء';
        } elseif ($e instanceof ValidationException) {
            $statusCode = 422;
            $message =  'خطأ في التحقق من صحة البيانات.';
            $errors = $e->errors();
        } elseif ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: 'حدث خطأ في الطلب';
        } else {
            if (config('app.debug')) {
                $message = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            } else {
                $message =  'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً.';
            }
        }

        return $this->errorResponse($message, $statusCode, $errors);
    }
}
