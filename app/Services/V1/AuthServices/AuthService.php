<?php

namespace App\Services\V1\AuthServices;


use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class AuthService{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'is_active' => true,
        ]);
        $user->assignRole('customer');
        return $user;
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if(! $user || ! Hash::check($credentials['password'], $user->password)){
             throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        if(!$user->is_active){
            throw ValidationException::withMessages([
                'email' => ['User is not active.'],
            ]);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return ['user' => $user,'token' => $token];
    }

    public function logout(User $user){
       $user->tokens()->delete();
    }

    public function changePassword(User $user, array $data){
        if(!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }
        $user->update(['password' => $data['password']]);
    }
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $avatar->store('avatars', 'public');
        }
        $user->update($data);
        return $user->fresh();
    }
}