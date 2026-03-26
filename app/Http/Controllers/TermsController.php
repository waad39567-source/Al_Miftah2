<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    use ApiResponseTrait;

    public function show(): JsonResponse
    {
        $term = Term::first();

        if (!$term) {
            return $this->errorResponse('الشروط والأحكام غير موجودة', 404);
        }

        return $this->successResponse($term);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->errorResponse('غير مصرح', 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'required|string',
        ]);

        $term = Term::first();

        if (!$term) {
            $term = Term::create([
                'title' => $request->title ?? 'الشروط والأحكام',
                'content' => $request->content,
            ]);
        } else {
            $term->update($request->only(['title', 'content']));
        }

        return $this->successResponse($term, 'تم تحديث الشروط والأحكام بنجاح');
    }
}
