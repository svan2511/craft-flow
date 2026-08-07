<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard,
    ) {}

    public function summary(): JsonResponse
    {
        try {
            return ApiResponse::success('Dashboard summary.', $this->dashboard->summary());
        } catch (Throwable $e) {
            return $this->apiError($e, 'DashboardController@summary');
        }
    }
}
