<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Actions\Cart\AddToCartAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get the authenticated user's cart.
     */
    public function show(): CartResource
    {
        $userId = auth()->id();
        $cart = $this->cartService->getOrCreateCart($userId);
        $cart->load(['items.product.images' => function ($query) {
            $query->where('is_primary', true);
        }]);
        $cart->subtotal = $this->cartService->getSubtotal($cart);

        return new CartResource($cart);
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(AddToCartRequest $request, AddToCartAction $action): JsonResponse
    {
        $dto = $request->toDTO(auth()->id());
        $action->execute($dto);

        return response()->json(['message' => 'Item added to cart successfully.']);
    }

    /**
     * Update an item's quantity in the cart.
     */
    public function updateItem(int $cartItemId, UpdateCartItemRequest $request, UpdateCartItemAction $action): JsonResponse
    {
        $dto = $request->toDTO(auth()->id());
        $action->execute($cartItemId, $dto);

        return response()->json(['message' => 'Cart item updated successfully.']);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId, RemoveCartItemAction $action): JsonResponse
    {
        $action->execute($cartItemId, auth()->id());

        return response()->json(['message' => 'Cart item removed successfully.']);
    }

    /**
     * Clear the user's cart.
     */
    public function clearCart(ClearCartAction $action): JsonResponse
    {
        $action->execute(auth()->id());

        return response()->json(['message' => 'Cart cleared successfully.']);
    }
}
