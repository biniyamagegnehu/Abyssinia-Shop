<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterUserDTO;
use App\Services\Auth\AuthService;

class RegisterUserAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Execute the register user action.
     *
     * @return array{user: \App\Models\User, token: string}
     */
    public function execute(RegisterUserDTO $dto): array
    {
        return $this->authService->register($dto);
    }
}
