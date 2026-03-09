<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionRequest;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use App\Services\RegionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private RegionService $regionService
    ) {}

    private function checkAdmin(Request $request): ?JsonResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'type', 'parent_id', 'search', 'has_children', 'sort_by', 'sort_order', 'per_page'
        ]);

        $regions = $this->regionService->getAll($filters);

        return $this->successResponse(RegionResource::collection($regions));
    }

    public function show(int $id): JsonResponse
    {
        $region = $this->regionService->getById($id);

        if (!$region) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        return $this->successResponse(new RegionResource($region));
    }

    public function store(RegionRequest $request): JsonResponse
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        if ($request->parent_id == $request->id) {
            return $this->errorResponse('لا يمكن جعل المنطقة كأب لنفسها', 422);
        }

        $region = $this->regionService->create($request->validated());

        return $this->successResponse(
            new RegionResource($region),
            'تم إنشاء المنطقة بنجاح',
            201
        );
    }

    public function update(RegionRequest $request, int $id): JsonResponse
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        $region = $this->regionService->getById($id);

        if (!$region) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        if ($request->parent_id == $id) {
            return $this->errorResponse('لا يمكن جعل المنطقة كأب لنفسها', 422);
        }

        $region = $this->regionService->update($region, $request->validated());

        return $this->successResponse(
            new RegionResource($region),
            'تم تحديث المنطقة بنجاح'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        $region = $this->regionService->getById($id);

        if (!$region) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        if (!$this->regionService->delete($region)) {
            if ($region->children()->count() > 0) {
                return $this->errorResponse('لا يمكن حذف منطقة لها مناطق فرعية', 422);
            }
            if ($region->properties()->count() > 0) {
                return $this->errorResponse('لا يمكن حذف منطقة مرتبطة بعقارات', 422);
            }
        }

        return $this->successResponse(null, 'تم حذف المنطقة بنجاح');
    }

    public function types(): JsonResponse
    {
        return $this->successResponse(Region::getTypes());
    }

    public function rootRegions(): JsonResponse
    {
        $regions = $this->regionService->getRootRegions();

        return $this->successResponse(RegionResource::collection($regions));
    }

    public function children(int $id): JsonResponse
    {
        $children = $this->regionService->getChildren($id);

        if ($children === null) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        return $this->successResponse(RegionResource::collection($children));
    }
}
