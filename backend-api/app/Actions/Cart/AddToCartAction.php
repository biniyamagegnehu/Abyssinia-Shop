<?php

namespace App\Actions\Cart;

use App\DTOs\Cart\AddToCartDTO;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class AddToCartAction
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function execute(AddToCartDTO $dto): CartItem
    {
        return DB::transaction(function () use ($dto) {
            return $this->cartService->addItem($dto);
        });
    }
}
