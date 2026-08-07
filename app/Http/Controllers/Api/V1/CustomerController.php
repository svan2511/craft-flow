<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Repositories\CustomerRepository;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerRepository $customers,
    ) {}

    public function index(): JsonResponse
    {
        try {
            return ApiResponse::success('Customers fetched.', [
                'customers' => CustomerResource::collection($this->customers->all()),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'CustomerController@index');
        }
    }

    public function search(): JsonResponse
    {
        try {
            $phone = preg_replace('/\D/', '', (string) request('phone'));

            if ($phone === '' || mb_strlen($phone) < 5) {
                return ApiResponse::success('No search term.', ['customers' => []]);
            }

            return ApiResponse::success('Customers fetched.', [
                'customers' => CustomerResource::collection(
                    collect([$this->customers->findByPhone($phone)])
                        ->filter()
                        ->values(),
                ),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'CustomerController@search');
        }
    }
}
