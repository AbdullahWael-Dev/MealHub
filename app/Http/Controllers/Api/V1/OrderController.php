<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Services\V1\OrderServices\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
        ]);
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->when(
                $request->filled('status'),
                fn($query) => $query->where('status', $request->string('status'))
            )
            ->latest()
            ->paginate();
        return $this->successResponse([
            'orders' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 'Orders retrieved successfully.');
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->with('items')
            ->findOrFail($order);

        return $this->successResponse(
            new OrderResource($order),
            'Order retrieved successfully.'
        );
    }


    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new OrderResource($order),
            'Order created successfully.',
            201
        );
    }

    public function cancel(Request $request, int $orderId): JsonResponse
    {
        $order = $this->orderService->cancelOrder($request->user(), $orderId);

        return $this->successResponse(
            new OrderResource($order),
            'Order cancelled successfully.'
        );
    }
}
