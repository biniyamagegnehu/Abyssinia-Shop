<?php

namespace App\Actions\Cart;

use App\DTOs\Cart\UpdateCartItemDTO;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function execute(int $cartItemId, UpdateCartItemDTO $dto): CartItem
    {
        return DB::transaction(function () use ($cartItemId, $dto) {
            return $this->cartService->updateItemQuantity($cartItemId, $dto);
        });
    }
}
