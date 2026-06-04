<?php

namespace App\Actions\Cart;

use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function execute(int $cartItemId, int $userId): void
    {
        DB::transaction(function () use ($cartItemId, $userId) {
            $this->cartService->removeItem($cartItemId, $userId);
        });
    }
}
