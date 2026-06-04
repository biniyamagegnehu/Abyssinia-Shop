<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Actions\Order\UpdateOrderStatusAction;
use App\DTOs\Order\UpdateOrderStatusDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Services\Order\OrderService;
use App\Traits\HasApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AdminOrderController extends Controller
{
    use HasApiResponses;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly UpdateOrderStatusAction $updateOrderStatusAction
    ) {
    }

    /**
     * Display a listing of all orders.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 15);

        $orders = $this->orderService->getAdminOrders($search, $status, $perPage);
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
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getAdminOrder($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        return $this->successResponse(
            new OrderResource($order),
            'Order retrieved successfully.'
        );
    }

    /**
     * Update the specified order's status.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->getAdminOrder($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        try {
            $dto = UpdateOrderStatusDTO::fromRequest($request->validated());
            $updatedOrder = $this->updateOrderStatusAction->execute($order, $dto);

            return $this->successResponse(
                new OrderResource($updatedOrder),
                'Order status updated successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
