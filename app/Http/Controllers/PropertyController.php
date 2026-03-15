<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertySimpleResource;
use App\Models\Property;
use App\Services\PropertyService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private PropertyService $propertyService)
    {
    }

    public function index(Request $request)
    {
        if ($request->user() && !Gate::allows('viewAny', Property::class)) {
            return $this->errorResponse('غير مصرح لك بعرض العقارات', 403);
        }

        $filters = $request->only([
            'type', 'property_type', 'region_id', 'region_ids',
            'status', 'search', 'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]);

        if (!isset($filters['status'])) {
            $filters['status'] = 'active';
        }

        $properties = $this->propertyService->getAll($filters);

        return $this->successResponse([
            'data' => PropertySimpleResource::collection($properties),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }

    public function search(Request $request)
    {
        if ($request->user() && !Gate::allows('viewAny', Property::class)) {
            return $this->errorResponse('غير مصرح لك بالبحث', 403);
        }

        $filters = $request->only([
            'type', 'property_type', 'region_id', 'region_ids',
            'status', 'search', 'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]);

        $properties = $this->propertyService->search($filters);

        return $this->successResponse($properties);
    }

    public function advancedSearch(Request $request)
    {
        if ($request->user() && !Gate::allows('viewAny', Property::class)) {
            return $this->errorResponse('غير مصرح لك بالبحث', 403);
        }

        $filters = $request->only([
            'type', 'property_type', 
            'governorate_id', 'city_id', 'neighborhood_id',
            'region_ids', 'region_names',
            'status', 'search', 'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]);

        $properties = $this->propertyService->advancedSearch($filters);

        return $this->successResponse($properties);
    }

    public function store(PropertyRequest $request)
    {
        if (!Gate::allows('create', Property::class)) {
            return $this->errorResponse('غير مصرح لك بإنشاء عقار', 403);
        }

        $property = $this->propertyService->create($request->validated(), $request->user()->id);

        if ($request->hasFile('images')) {
            $this->propertyService->addImages($property, $request->file('images'));
        }

        if ($request->has('images_base64')) {
            $base64Images = $request->input('images_base64');
            if (is_array($base64Images)) {
                $this->propertyService->addImagesFromBase64($property, $base64Images);
            }
        }

        return $this->successResponse(
            new PropertyResource($property->load(['owner', 'region', 'images'])),
            'تم إنشاء العقار بنجاح',
            201
        );
    }

    public function show(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if ($request->user() && !Gate::allows('view', $property)) {
            return $this->errorResponse('غير مصرح لك بعرض هذا العقار', 403);
        }

        $contactInfo = null;
        if ($request->user()) {
            $contactRequest = $this->propertyService->getUserContactRequest(
                $request->user()->id,
                $id
            );

            if ($contactRequest && $contactRequest->status === 'approved') {
                $contactInfo = [
                    'owner_name' => $property->owner->name,
                    'owner_phone' => $property->owner->phone,
                ];
            }
        }

        return $this->successResponse([
            'property' => new PropertyResource($property),
            'contact_info' => $contactInfo
        ]);
    }

    public function update(PropertyRequest $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('update', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لتعديل هذا العقار', 403);
        }

        $this->propertyService->update($property, $request->validated());

        if ($request->hasFile('images')) {
            $this->propertyService->addImages($property, $request->file('images'));
        }

        if ($request->has('images_base64')) {
            $base64Images = $request->input('images_base64');
            if (is_array($base64Images)) {
                $this->propertyService->addImagesFromBase64($property, $base64Images);
            }
        }

        return $this->successResponse(
            new PropertyResource($property->fresh(['owner', 'region', 'images'])),
            'تم تحديث العقار بنجاح'
        );
    }

    public function destroy(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('delete', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لحذف هذا العقار', 403);
        }

        $this->propertyService->delete($property);

        return $this->successResponse(null, 'تم حذف العقار بنجاح');
    }

    public function myProperties(Request $request)
    {
        $filters = array_merge($request->only([
            'type', 'region_id', 'status', 'search', 
            'sort_by', 'sort_order', 'per_page',
            'min_price', 'max_price', 'min_area', 'max_area'
        ]), [
            'owner_id' => $request->user()->id
        ]);

        $properties = $this->propertyService->getAllForAdmin($filters);

        return $this->successResponse($properties);
    }

    public function deleteImage(Request $request, $id, $imageId)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('deleteImage', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لحذف صورة هذا العقار', 403);
        }

        $deleted = $this->propertyService->deleteImage($property, $imageId);

        if (!$deleted) {
            return $this->errorResponse('الصورة غير موجودة', 404);
        }

        return $this->successResponse(null, 'تم حذف الصورة بنجاح');
    }

    public function markAsRented(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('markAsRented', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لتعديل هذا العقار', 403);
        }

        $this->propertyService->markAsRented($property);

        return $this->successResponse(null, 'تم تعليم العقار كمؤجر');
    }

    public function markAsSold(Request $request, $id)
    {
        $property = $this->propertyService->getById($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        if (!Gate::allows('markAsSold', $property)) {
            return $this->errorResponse('ليس لديك صلاحية لتعديل هذا العقار', 403);
        }

        $this->propertyService->markAsSold($property);

        return $this->successResponse(null, 'تم تعليم العقار كمباع');
    }
}
