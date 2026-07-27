<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddToCartRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartItemResource;
use App\Services\V1\CartService\CartService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->cartSummary($request->user());

        return $this->successResponse(
            [
                'items' => CartItemResource::collection($cart['items']),
                'summary' => $cart['summary'],
            ],
            'Cart retrieved successfully'
        );
    }

    public function addItem(AddToCartRequest $request): JsonResponse
    {
        try {
            $cartItem = $this->cartService->addItem(
                $request->user(),
                $request->meal_id,
                $request->quantity
            );
            return $this->successResponse(
                new CartItemResource($cartItem),
                'Item added to cart successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                null,
                $e->getMessage(),
                500
            );
        }
    }

    public function updateItem(UpdateCartItemRequest $request, int $mealId): JsonResponse
    {
        try {
            $cartItem = $this->cartService->updateQuantity(
                $request->user(),
                $mealId,
                $request->quantity
            );

            return $this->successResponse(
                new CartItemResource($cartItem),
                'Cart item updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                null,
                $e->getMessage(),
               500
            );
        }
    }

    public function removeItem(Request $request, int $mealId): JsonResponse
    {
        try {
            $this->cartService->removeItem($request->user(), $mealId);

            return $this->successResponse(
                null,
                'Item removed from cart successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                null,
                $e->getMessage(),
                500
            );
        }
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user());

        return $this->successResponse(
            null,
            'Cart cleared successfully'
        );
    }
}