<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Resources\WorkshopResource;
use App\Services\WorkshopService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class WorkshopController extends Controller
{
    public function __construct(
        protected WorkshopService $workshops,
    ) {}

    public function store(StoreWorkshopRequest $request): JsonResponse
    {
        try {
            $workshop = $this->workshops->create(auth()->user(), $request->validated());

            return ApiResponse::success('Workshop created successfully.', [
                'workshop' => new WorkshopResource($workshop),
            ], 201);
        } catch (Throwable $e) {
            return $this->apiError($e, 'WorkshopController@store');
        }
    }
}
