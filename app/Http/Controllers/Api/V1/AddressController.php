<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAddressRequest;
use App\Http\Requests\Api\V1\UpdateAddressRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Services\V1\AddressServices\AddressService;
use App\Models\Address;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{

    use ApiResponseTrait;
    public function __construct(protected AddressService $addressService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 50); 

        $filters = [
            'city'   => $request->query('city'),
            'search' => $request->query('search'),
        ];

        $addresses = $this->addressService->listForUser($request->user(), $filters, $perPage);

        return response()->json([
            'data' => AddressResource::collection($addresses->items()),
            'meta' => [
                'current_page' => $addresses->currentPage(),
                'last_page'    => $addresses->lastPage(),
                'per_page'     => $addresses->perPage(),
                'total'        => $addresses->total(),
            ],
            'links' => [
                'first' => $addresses->url(1),
                'last'  => $addresses->url($addresses->lastPage()),
                'prev'  => $addresses->previousPageUrl(),
                'next'  => $addresses->nextPageUrl(),
            ],
        ]);
    }
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(new AddressResource($address), 'Address created successfully', 201);
    }
    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);

        return $this->successResponse(new AddressResource($address), 'Address retrieved successfully');
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);

        $updatedAddress = $this->addressService->update($address, $request->validated());

        return $this->successResponse(new AddressResource($updatedAddress), 'Address updated successfully');
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);

        $this->addressService->delete($address);

        return $this->successResponse(null, 'Address deleted successfully');
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);

        $address = $this->addressService->makeDefault($address);

        return $this->successResponse(new AddressResource($address), 'Address set as default successfully');
    }
    protected function authorizeOwnership(Request $request, Address $address): void
    {
        if ($request->user()->id !== $address->user_id) {
            abort(403);
        }
    }
}
