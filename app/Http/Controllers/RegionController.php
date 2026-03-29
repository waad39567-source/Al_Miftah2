<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionRequest;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use App\Services\RegionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Regions",
 *     description="إدارة المناطق"
 * )
 */
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
        if ($request->type !== 'neighborhood') {
            $check = $this->checkAdmin($request);
            if ($check) return $check;
        }

        if ($request->parent_id && $request->parent_id == $request->id) {
            return $this->errorResponse('لا يمكن جعل المنطقة كأب لنفسها', 422);
        }

        $validationError = $this->validateRegionHierarchy($request->type, $request->parent_id);
        if ($validationError) {
            return $validationError;
        }

        $region = $this->regionService->create($request->validated());

        return $this->successResponse(
            new RegionResource($region),
            'تم إنشاء المنطقة بنجاح',
            201
        );
    }

    private function validateRegionHierarchy(?string $type, ?int $parentId): ?JsonResponse
    {
        if (!$type) {
            return null;
        }

        switch ($type) {
            case 'country':
                if ($parentId !== null) {
                    return $this->errorResponse('الدولة لا يمكن أن تكون لها منطقة أب', 422);
                }
                break;

            case 'governorate':
                if ($parentId === null) {
                    return $this->errorResponse('المحافظة تحتاج إلى تحديد دولة كمنطقة أب', 422);
                }
                $parent = Region::find($parentId);
                if (!$parent || $parent->type !== 'country') {
                    return $this->errorResponse('المحافظة يجب أن تكون داخل دولة', 422);
                }
                break;

            case 'city':
                if ($parentId === null) {
                    return $this->errorResponse('المدينة تحتاج إلى تحديد محافظة كمنطقة أب', 422);
                }
                $parent = Region::find($parentId);
                if (!$parent || $parent->type !== 'governorate') {
                    return $this->errorResponse('المدينة يجب أن تكون داخل محافظة', 422);
                }
                break;

            case 'neighborhood':
                if ($parentId === null) {
                    return $this->errorResponse('الحي يحتاج إلى تحديد مدينة كمنطقة أب', 422);
                }
                $parent = Region::find($parentId);
                if (!$parent || $parent->type !== 'city') {
                    return $this->errorResponse('الحي يجب أن يكون داخل مدينة', 422);
                }
                break;
        }

        return null;
    }

    public function update(RegionRequest $request, int $id): JsonResponse
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        $region = $this->regionService->getById($id);

        if (!$region) {
            return $this->errorResponse('المنطقة غير موجودة', 404);
        }

        if ($request->parent_id && $request->parent_id == $id) {
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

    public function tree(): JsonResponse
    {
        $regions = $this->regionService->getRootRegionsWithNestedChildren();

        return $this->successResponse(RegionResource::collection($regions));
    }
}
