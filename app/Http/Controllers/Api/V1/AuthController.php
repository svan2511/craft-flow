<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\WorkshopResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->auth->sendOtp($request->validated('phone'));

            $data = [
                'phone' => $request->validated('phone'),
                'retry_in' => $result['retry_in'],
                'expires_in' => 300,
            ];

            if ($result['dev_otp'] !== null) {
                $data['dev_otp'] = $result['dev_otp'];
            }

            return ApiResponse::success('Anil Test.', $data);
        } catch (Throwable $e) {
            return $this->apiError($e, 'AuthController@sendOtp');
        }
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->auth->verifyOtp(
                $request->validated('phone'),
                $request->validated('otp'),
            );

            if ($result === null) {
                return ApiResponse::error('Invalid or expired OTP.', 401);
            }

            return ApiResponse::success('OTP verified successfully.', [
                'token' => $result['token'],
                'user' => new UserResource($result['user']),
                'workshop' => $result['user']->workshop
                    ? new WorkshopResource($result['user']->workshop)
                    : null,
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'AuthController@verifyOtp');
        }
    }

    public function me(): JsonResponse
    {
        try {
            return ApiResponse::success('Authenticated user.', [
                'user' => new UserResource(auth()->user()),
            ]);
        } catch (Throwable $e) {
            return $this->apiError($e, 'AuthController@me');
        }
    }

    public function logout(): JsonResponse
    {
        try {
            auth()->user()->currentAccessToken()->delete();

            return ApiResponse::success('Logged out successfully.');
        } catch (Throwable $e) {
            return $this->apiError($e, 'AuthController@logout');
        }
    }
}
