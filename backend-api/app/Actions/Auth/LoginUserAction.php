<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginUserDTO;
use App\Services\Auth\AuthService;

class LoginUserAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Execute the login action.
     *
     * @return array{user: \App\Models\User, token: string}
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(LoginUserDTO $dto): array
    {
        return $this->authService->login($dto);
    }
}
