<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Auth\AuthService;

class LogoutUserAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Execute the logout action.
     */
    public function execute(User $user): void
    {
        $this->authService->logout($user);
    }
}
