<?php

namespace App\Actions\Cart;

use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function execute(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $this->cartService->clearCart($userId);
        });
    }
}
