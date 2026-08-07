<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettleWeeklyRequest;
use App\Http\Requests\StoreKarigarAdvanceRequest;
use App\Http\Requests\StoreKarigarRequest;
use App\Http\Resources\KarigarResource;
use App\Http\Resources\PaymentResource;
use App\Services\KarigarService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class KarigarController extends Controller
{
    public function __construct(
        protected KarigarService $karigars,
    ) {}

    public function index(): JsonResponse
    {
        try {
            return ApiResponse::success('Karigars fetched.', [
                'karigars' => KarigarResource::collection($this->karigars->index()),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'KarigarController@index');
        }
    }

    public function store(StoreKarigarRequest $request): JsonResponse
    {
        try {
            $karigar = $this->karigars->store($request->validated());

            return ApiResponse::success('Karigar added successfully.', [
                'karigar' => new KarigarResource($karigar->load('orders', 'payments')),
            ], 201);
        } catch (Throwable $e) {
            return $this->apiError($e, 'KarigarController@store');
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $karigar = $this->karigars->show($id);

            if ($karigar === null) {
                return ApiResponse::notFound('Karigar not found.');
            }

            return ApiResponse::success('Karigar fetched.', [
                'karigar' => new KarigarResource($karigar),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'KarigarController@show');
        }
    }

    public function giveAdvance(int $id, StoreKarigarAdvanceRequest $request): JsonResponse
    {
        try {
            $karigar = $this->karigars->show($id);

            if ($karigar === null) {
                return ApiResponse::notFound('Karigar not found.');
            }

            $payment = $this->karigars->giveAdvance($karigar, $request->validated());

            return ApiResponse::success('Advance recorded successfully.', [
                'payment' => new PaymentResource($payment),
                'karigar' => new KarigarResource($karigar->fresh(['orders.stages', 'workOrders.stages', 'payments.stage', 'payments.order'])),
            ], 201);
        } catch (Throwable $e) {
            return $this->apiError($e, 'KarigarController@giveAdvance');
        }
    }

    public function settleWeekly(int $id, SettleWeeklyRequest $request): JsonResponse
    {
        try {
            $karigar = $this->karigars->show($id);

            if ($karigar === null) {
                return ApiResponse::notFound('Karigar not found.');
            }

            $payment = $this->karigars->settleWeekly($karigar, $request->validated());

            if ($payment === null) {
                return ApiResponse::error('No positive balance to settle. Advances taken exceed this week\'s earnings.', 422);
            }

            return ApiResponse::success('Weekly settlement recorded.', [
                'payment' => new PaymentResource($payment),
                'karigar' => new KarigarResource($karigar->fresh(['orders.stages', 'workOrders.stages', 'payments.stage', 'payments.order'])),
            ], 201);
        } catch (Throwable $e) {
            return $this->apiError($e, 'KarigarController@settleWeekly');
        }
    }
}
