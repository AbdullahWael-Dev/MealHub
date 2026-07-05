<?php

namespace App\Services\V1\AddressServices;

use App\Models\Address;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function listForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
{
    return $user->addresses()
        ->when($filters['city'] ?? null, function ($query, $city) {
            $query->where('city', $city);
        })
        ->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('street', 'like', "%{$search}%");
            });
        })
        ->orderByDesc('is_default')
        ->orderByDesc('id')
        ->paginate($perPage);
}

    public function create(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $isFirstAddress = !$user->addresses()->exists();
            $data['is_default'] = $isFirstAddress ? true : (bool) ($data['is_default'] ?? false);
            $address = $user->addresses()->create($data);
            if ($address->is_default) {
                $this->makeDefault($address);
            }
            return $address->refresh();
        });
    }
     public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $address->update($data);

            if (array_key_exists('is_default', $data) && $data['is_default']) {
                $this->makeDefault($address);
            }

            return $address->refresh();
        });
    }
    public function delete(Address $address): bool
    {
        return DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $userId = $address->user_id;

            $deleted = $address->delete();
            if ($wasDefault) {
                $next = Address::where('user_id', $userId)
                    ->orderByDesc('id')
                    ->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }

            return $deleted;
        });
    }

    public function makeDefault(Address $address): Address
    {
        return DB::transaction(function () use ($address) {
            Address::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            if (!$address->is_default) {
                $address->update(['is_default' => true]);
            }

            return $address->refresh();
        });
    }
}
