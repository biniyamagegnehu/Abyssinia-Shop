<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Services\Order\OrderService;
use App\Traits\HasApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use HasApiResponses;

    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    /**
     * Display a listing of the customer's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $orders = $this->orderService->getCustomerOrders($request->user()->id, $perPage);
        $collection = OrderResource::collection($orders);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data' => $collection->resolve($request),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getCustomerOrder($id, $request->user()->id);

        if (!$order) {
            return $this->errorResponse('You do not have access to this order', 403);
        }

        return $this->successResponse(
            new OrderResource($order),
            'Order retrieved successfully.'
        );
    }
}
