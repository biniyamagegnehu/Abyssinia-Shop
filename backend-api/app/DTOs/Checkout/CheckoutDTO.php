<?php

namespace App\DTOs\Checkout;

class CheckoutDTO
{
    public function __construct(
        public readonly int $shippingAddressId,
        public readonly int $billingAddressId,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            shippingAddressId: $data['shipping_address_id'],
            billingAddressId: $data['billing_address_id'],
        );
    }
}
