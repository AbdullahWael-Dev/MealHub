<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\V1\AuthServices\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private readonly AuthService $authService)
    {

    }


    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        return $this->successResponse(
            data: new UserResource($user),
            message: 'User registered successfully',
            code: 201
        );
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        return $this->successResponse(
            data: [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            message: 'User logged in successfully',
            code: 200
        );
    }
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse(
            message: 'User logged out successfully',
            code: 200
        );
    }
}
