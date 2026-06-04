<?php

namespace App\Http\Controllers\Api\V1\Checkout;

use App\Actions\Checkout\PlaceOrderAction;
use App\DTOs\Checkout\CheckoutDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Http\Resources\Checkout\CheckoutSummaryResource;
use App\Http\Resources\Checkout\OrderResource;
use App\Models\Cart;
use App\Services\Checkout\CheckoutService;
use App\Traits\HasApiResponses;
use Illuminate\Http\JsonResponse;
use Exception;

class CheckoutController extends Controller
{
    use HasApiResponses;

    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly PlaceOrderAction $placeOrderAction
    ) {
    }

    /**
     * Get checkout summary before placing order
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        if ($cart->items()->count() === 0) {
            return $this->errorResponse('Your cart is empty', 400);
        }

        $summary = $this->checkoutService->calculateSummary($cart);

        return $this->successResponse(
            new CheckoutSummaryResource($summary),
            'Checkout summary retrieved successfully.'
        );
    }

    /**
     * Place the order
     */
    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        try {
            $dto = CheckoutDTO::fromRequest($request->validated());
            $order = $this->placeOrderAction->execute($cart, $dto);

            return $this->successResponse(
                new OrderResource($order),
                'Order placed successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
