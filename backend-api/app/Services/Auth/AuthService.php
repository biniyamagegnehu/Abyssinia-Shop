<?php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginUserDTO;
use App\DTOs\Auth\RegisterUserDTO;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    /**
     * Register a new user and assign Customer role.
     *
     * @return array{user: User, token: string}
     */
    public function register(RegisterUserDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // Create user
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'status' => UserStatus::ACTIVE,
            ]);

            // Ensure role exists and assign it
            $role = Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
            $user->assignRole($role);

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * Authenticate user credentials and check status.
     *
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function login(LoginUserDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        // Check user existence and password matching
        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        // Verify that account is active
        if ($user->status !== UserStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'status' => ['Your account is currently ' . $user->status->value . '.'],
            ]);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke current user session access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
