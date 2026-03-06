<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function successResponse($data = null, string $message = 'تمت العملية بنجاح', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    public function errorResponse(string $message = 'حدث خطأ ما', int $statusCode = 400, $errors = null, ?string $debug = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($debug !== null && config('app.debug')) {
            $response['debug'] = $debug;
        }

        return response()->json($response, $statusCode);
    }
}