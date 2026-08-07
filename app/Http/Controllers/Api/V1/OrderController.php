<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StoreOrderStageRequest;
use App\Http\Requests\UpdateOrderCostingRequest;
use App\Http\Requests\UpdateOrderKarigarRequest;
use App\Http\Requests\UpdateOrderStageRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orders,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $list = $this->orders->index($request->query('status'));

            return ApiResponse::success('Orders fetched.', [
                'orders' => OrderResource::collection($list),
                'count' => $list->count(),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@index');
        }
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orders->store(
                $request->validated(),
                (bool) $request->boolean('send_whatsapp'),
            );

            return ApiResponse::success('Order created successfully.', [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ], 201);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@store');
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orders->show($id);

            if ($order === null) {
                return ApiResponse::notFound('Order not found.');
            }

            return ApiResponse::success('Order fetched.', [
                'order' => new OrderDetailResource($order),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@show');
        }
    }

    public function updateStatus(int $id, UpdateOrderStatusRequest $request): JsonResponse
    {
        try {
            $order = $this->orders->updateStatus(
                $id,
                $request->validated('status'),
                $request->input('worker_labor_cost') !== null ? (float) $request->input('worker_labor_cost') : null,
            );

            if ($order === null) {
                return ApiResponse::notFound('Order not found.');
            }

            return ApiResponse::success('Order status updated.', [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@updateStatus');
        }
    }

    public function assignKarigar(int $id, UpdateOrderKarigarRequest $request): JsonResponse
    {
        try {
            $order = $this->orders->assignKarigar(
                $id,
                $request->input('karigar_id'),
            );

            if ($order === null) {
                return ApiResponse::notFound('Order not found.');
            }

            $message = $order->karigar_id === null
                ? 'Karigar unassigned from order.'
                : 'Karigar assigned to order.';

            return ApiResponse::success($message, [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@assignKarigar');
        }
    }

    public function updateCosting(int $order, UpdateOrderCostingRequest $request): JsonResponse
    {
        try {
            $order = $this->orders->updateCosting($order, $request->validated());

            if ($order === null) {
                return ApiResponse::notFound('Order not found.');
            }

            return ApiResponse::success('Costing updated.', [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@updateCosting');
        }
    }

    public function addStage(int $order, StoreOrderStageRequest $request): JsonResponse
    {
        try {
            $order = $this->orders->addStage($order, $request->validated());

            if ($order === null) {
                return ApiResponse::notFound('Order not found.');
            }

            return ApiResponse::success('Stage added to order.', [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@addStage');
        }
    }

    public function updateStage(int $order, int $stage, UpdateOrderStageRequest $request): JsonResponse
    {
        try {
            $order = $this->orders->updateStage($order, $stage, $request->validated());

            if ($order === null) {
                return ApiResponse::notFound('Order or stage not found.');
            }

            return ApiResponse::success('Stage updated.', [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@updateStage');
        }
    }

    public function deleteStage(int $order, int $stage): JsonResponse
    {
        try {
            $order = $this->orders->deleteStage($order, $stage);

            if ($order === null) {
                return ApiResponse::notFound('Order or stage not found.');
            }

            return ApiResponse::success('Stage removed.', [
                'order' => new OrderDetailResource($order->load(['customer', 'karigar', 'payments', 'stages.karigar'])),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'OrderController@deleteStage');
        }
    }
}
