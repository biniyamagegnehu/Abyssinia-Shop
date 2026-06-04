<?php

namespace App\Services\Cart;

use App\DTOs\Cart\AddToCartDTO;
use App\DTOs\Cart\UpdateCartItemDTO;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected CartItemRepositoryInterface $cartItemRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get or create a cart for the user.
     */
    public function getOrCreateCart(int $userId): Cart
    {
        $cart = $this->cartRepository->findByUser($userId);

        if (!$cart) {
            $cart = $this->cartRepository->createForUser($userId);
        }

        return $cart;
    }

    /**
     * Get the user's cart subtotal.
     */
    public function getSubtotal(Cart $cart): float
    {
        $items = $this->cartItemRepository->getCartItems($cart->id);
        
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        return $subtotal;
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(AddToCartDTO $dto): CartItem
    {
        $product = $this->productRepository->findById($dto->product_id);

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => 'Product not found',
            ]);
        }

        if ($product->status === ProductStatus::ARCHIVED) {
            throw ValidationException::withMessages([
                'product_id' => 'Product is unavailable',
            ]);
        }

        if ($product->status === ProductStatus::OUT_OF_STOCK || $product->quantity <= 0) {
            throw ValidationException::withMessages([
                'product_id' => 'Requested quantity exceeds available stock',
            ]);
        }

        $cart = $this->getOrCreateCart($dto->user_id);
        
        // Check if item already exists in cart
        $existingItem = $this->cartItemRepository->findByProduct($cart->id, $product->id);

        $newQuantity = $dto->quantity;
        
        if ($existingItem) {
            $newQuantity += $existingItem->quantity;
        }

        // Validate stock against total quantity
        if ($newQuantity > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Requested quantity exceeds available stock',
            ]);
        }

        if ($existingItem) {
            $this->cartItemRepository->update($existingItem->id, [
                'quantity' => $newQuantity,
                'price' => $product->price // Update price to current
            ]);
            return $this->cartItemRepository->findById($existingItem->id);
        }

        return $this->cartItemRepository->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $newQuantity,
            'price' => $product->price
        ]);
    }

    /**
     * Update an item's quantity in the cart.
     */
    public function updateItemQuantity(int $cartItemId, UpdateCartItemDTO $dto): CartItem
    {
        $cartItem = $this->cartItemRepository->findById($cartItemId);

        if (!$cartItem) {
            throw ValidationException::withMessages([
                'cart_item' => 'Cart item not found',
            ]);
        }

        // Verify cart ownership
        if ($cartItem->cart->user_id !== $dto->user_id) {
            abort(403, 'Unauthorized access to cart item');
        }

        $product = $cartItem->product;

        if ($product->status === ProductStatus::ARCHIVED) {
            throw ValidationException::withMessages([
                'cart_item' => 'Product is unavailable',
            ]);
        }

        if ($dto->quantity > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Requested quantity exceeds available stock',
            ]);
        }

        $this->cartItemRepository->update($cartItem->id, [
            'quantity' => $dto->quantity,
            'price' => $product->price // Update price to current
        ]);

        return $this->cartItemRepository->findById($cartItem->id);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId, int $userId): void
    {
        $cartItem = $this->cartItemRepository->findById($cartItemId);

        if (!$cartItem) {
            return;
        }

        if ($cartItem->cart->user_id !== $userId) {
            abort(403, 'Unauthorized access to cart item');
        }

        $this->cartItemRepository->delete($cartItemId);
    }

    /**
     * Clear the user's cart.
     */
    public function clearCart(int $userId): void
    {
        $cart = $this->cartRepository->findByUser($userId);
        
        if ($cart) {
            $this->cartRepository->clear($cart->id);
        }
    }
}
