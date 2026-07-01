<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\V1\AuthServices\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private readonly AuthService $authService) {}

    public function show(Request $request)
    {
        return $this->successResponse(
            data: new UserResource($request->user()),
            message: 'Profile retrieved successfully',
            code: 200
        );
    }
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->authService->updateProfile(
            user: $request->user(),
            data: $request->safe()->except('avatar'),
            avatar: $request->file('avatar'),
        );

        return $this->successResponse(
            data: new UserResource($user),
            message: 'Profile updated successfully',
            code: 200
        );
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword($request->user(), $request->validated());
        return $this->successResponse(
            message: 'Password changed successfully',
            code: 200
        );
    }
}
