<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceivePaymentRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\PaymentResource;
use App\Services\FinanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class FinanceController extends Controller
{
    public function __construct(
        protected FinanceService $finance,
    ) {}

    public function receivePayment(ReceivePaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->finance->receivePayment($request->validated());

            if ($payment === null) {
                return ApiResponse::notFound('Order not found.');
            }

            $order = $payment->order->load(['customer', 'karigar', 'payments']);

            return ApiResponse::success('Payment received successfully.', [
                'payment' => new PaymentResource($payment),
                'order' => new OrderDetailResource($order),
            ], 201);
        } catch (Throwable $e) {
            return $this->apiError($e, 'FinanceController@receivePayment');
        }
    }

    public function reportSummary(): JsonResponse
    {
        try {
            return ApiResponse::success('Report summary.', $this->finance->reportSummary());
        } catch (Throwable $e) {
            return $this->apiError($e, 'FinanceController@reportSummary');
        }
    }
}
