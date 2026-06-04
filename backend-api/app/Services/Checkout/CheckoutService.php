<?php

namespace App\Services\Checkout;

use App\Models\Cart;

class CheckoutService
{
    private const SHIPPING_FEE = 150.00;
    
    /**
     * Calculate checkout summary from cart
     */
    public function calculateSummary(Cart $cart): array
    {
        $items = $cart->items()->with('product')->get();
        
        $subtotal = 0;
        $itemsData = [];
        
        foreach ($items as $item) {
            $product = $item->product;
            
            $price = $product->price;
            $lineTotal = $price * $item->quantity;
            $subtotal += $lineTotal;
            
            $itemsData[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $item->quantity,
                'price' => $price,
                'line_total' => $lineTotal,
            ];
        }
        
        $shippingFee = count($itemsData) > 0 ? self::SHIPPING_FEE : 0.00;
        $discount = 0.00;
        $total = $subtotal + $shippingFee - $discount;
        
        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'total' => $total,
            'items' => $itemsData,
        ];
    }
}
