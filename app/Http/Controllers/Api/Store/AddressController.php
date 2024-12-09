<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Get the authenticated user's address
     */
    public function myAddress()
    {
        $user = auth('sanctum')->user();
        $address = $user->address;  // Assuming you have the relationship set up

        return $this->success($address, __('user address'));
    }

    /**
     * Update or create the authenticated user's address
     */
    public function updateAddress(Request $request)
    {
        try {
            $request->validate([
                'name' => 'nullable|sometimes|string|max:255',
                'email' => 'nullable|sometimes|email|max:255',
                'phone' => 'nullable|sometimes|string|max:255',
                'building_type' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'governorate' => 'required|string|max:255',
                'area' => 'required|string|max:255',
                'block' => 'required|string|max:255',
                'street' => 'nullable|string|max:255',
                'avenue' => 'nullable|string|max:255',
                'apartment' => 'nullable|string|max:255',
                'floor' => 'nullable|string|max:255',
                'house' => 'nullable|string|max:255',
                'comment' => 'nullable|string'
            ]);

            $user = $request->user();

            // If user has an address, update it; otherwise create new
            if ($user->address) {
                $address = $user->address;
                $address->update($request->all());
            } else {
                $address = Address::create($request->all());
                $user->address()->associate($address);
                $user->save();
            }

            return $this->success($address, __('address updated successfully'));
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), 422);
        }
    }
}
