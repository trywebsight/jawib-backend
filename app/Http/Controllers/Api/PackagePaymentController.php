<?php

namespace App\Http\Controllers\Api;

use App\Enums\TapStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Purchase;
use App\Services\TapPayment;

class PackagePaymentController extends Controller
{
    public function buy($id)
    {
        $package = Package::find($id);
        if (!$package) {
            return $this->error([], __("invalid package id"), 422);
        }
        $user = auth('sanctum')->user();

        $purchase = Purchase::firstOrCreate(
            [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'credits' => $package->games_count,
                'payment_status' => TapStatusEnum::INITIATED,
            ]
        );

        $tap = TapPayment::createCharge($this->paymentData($purchase));

        $purchase->tap_id = $tap['id'];
        $purchase->payment_link = $tap['transaction']['url'] ?? false;

        return $this->success($purchase);
    }

    // Handle payment callback
    public function callback(Request $request)
    {
        if (!$request->tap_id) {
            return $this->error([], __('invalid request id'));
        }

        $tap_response = TapPayment::retrieveCharge($request->tap_id);

        $purchase = Purchase::find($tap_response['metadata']['purchase_id'] ?? null);
        if (!$purchase) {
            return $this->error([], __('invalid purchase id'));
        }

        // Check if payment is already captured to avoid duplicate deposit
        if ($purchase->payment_status === TapStatusEnum::CAPTURED) {
            return $this->success([], __("payment already processed"));
        }

        $purchase->update([
            'tap_id' => $tap_response['id'] ?? $purchase->tap_id,
            'payment_status' => $tap_response['status'] ?? $purchase->payment_status,
        ]);

        if (isset($tap_response['status']) && $tap_response['status'] == TapStatusEnum::CAPTURED) {
            $purchase->user?->deposit($purchase->credits, ['description' => 'package purchase', 'purchase_id' => $purchase->id]);
            return $this->success([], __('paid successfully'));
        }
        return $this->error([], __("payment failed"));
    }

    // Tap charge request body data
    private function paymentData($purchase)
    {
        $user = $purchase->user;
        $package = $purchase->package;

        return [
            'amount' => $package->price,
            'currency' => 'KWD',
            'customer_initiated' => true,
            'threeDSecure' => false,
            'save_card' => false,
            'metadata' => [
                'purchase_id' => $purchase->id,
                'package_id' => $package->id,
                'user_id' => $user->id,
            ],
            'customer' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => [
                    'country_code' => 965,
                    'number' => 51234567,
                ],
            ],
            'source' => [
                'id' => 'src_all',
            ],
            'redirect' => [
                'url' => route('tap_callback'),
            ],
        ];
    }
}
