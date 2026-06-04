<?php

namespace App\Actions\Checkout;

use App\DTOs\Checkout\CheckoutDTO;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Checkout\CheckoutService;
use Illuminate\Support\Facades\DB;
use Exception;

class PlaceOrderAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly CheckoutService $checkoutService
    ) {
    }

    public function execute(Cart $cart, CheckoutDTO $dto)
    {
        // Validate cart
        if ($cart->items()->count() === 0) {
            throw new Exception("Your cart is empty");
        }

        // Validate stock and status
        $items = $cart->items()->with('product')->get();
        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) {
                throw new Exception("Product is no longer available.");
            }
            if ($product->status === ProductStatus::ARCHIVED) {
                throw new Exception("Product {$product->name} is archived.");
            }
            if ($product->status !== ProductStatus::ACTIVE) {
                throw new Exception("Product {$product->name} is not active.");
            }
            if ($product->quantity < $item->quantity) {
                throw new Exception("Insufficient stock for {$product->name}");
            }
        }

        // Calculate totals
        $summary = $this->checkoutService->calculateSummary($cart);

        return DB::transaction(function () use ($cart, $dto, $items, $summary) {
            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Create Order
            $order = $this->orderRepository->create([
                'user_id' => $cart->user_id,
                'shipping_address_id' => $dto->shippingAddressId,
                'billing_address_id' => $dto->billingAddressId,
                'order_number' => $orderNumber,
                'status' => OrderStatus::PENDING,
                'total_amount' => $summary['total'],
                'shipping_cost' => $summary['shipping_fee'],
                'tax_amount' => 0.00,
                'notes' => null,
            ]);

            // Create Order Items and reduce stock
            $orderItemsData = [];
            $now = now();
            foreach ($items as $item) {
                $product = $item->product;
                
                // create snapshot
                $orderItemsData[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // reduce stock
                $product->decrement('quantity', $item->quantity);
            }

            $this->orderItemRepository->insert($orderItemsData);

            // Clear cart items
            $cart->items()->delete();
            
            // Reload order to ensure everything is correct
            return $order->load('items');
        });
    }

    private function generateOrderNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = "ABS-{$today}-";
        
        $lastOrder = \App\Models\Order::where('order_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastSequence = (int) substr($lastOrder->order_number, -5);
            $newSequence = str_pad((string)($lastSequence + 1), 5, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '00001';
        }

        return $prefix . $newSequence;
    }
}
