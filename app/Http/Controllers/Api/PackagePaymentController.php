<?php

namespace App\Http\Controllers\Api;

use App\Enums\TapStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Transaction;
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

        $transaction = Transaction::firstOrCreate(
            [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'type' => 'purchase',
                'credit_change' => $package->games_count,
                'payment_status' => TapStatusEnum::INITIATED,
            ]
        );

        $tap = TapPayment::createCharge($this->paymentData($transaction));

        $transaction->tap_id = $tap['id'];
        $transaction->payment_link = $tap['transaction']['url'] ?? false;

        return $this->success($transaction);
    }

    public function callback(Request $request)
    {
        if (!$request->tap_id) {
            return $this->error([], __('invalid request id'));
        }

        $tap_response = TapPayment::retrieveCharge($request->tap_id);

        $transaction = Transaction::find($tap_response['metadata']['transaction_id'] ?? null);
        if (!$transaction) {
            return $this->error([], __('invalid transaction id'));
        }

        $transaction->update([
            'tap_id' => $tap_response['id'] ?? $transaction->tap_id,
            'payment_status' => $tap_response['status'] ?? $transaction->payment_status,
        ]);

        if (isset($tap_response['status']) && $tap_response['status'] == TapStatusEnum::CAPTURED) {
            return $this->success($transaction, __('paid successfully'));
        }
        return $this->error($transaction, __("payment failed"));
    }
    private function paymentData($transaction)
    {
        $user = $transaction->user;
        $package = $transaction->package;

        return [
            'amount' => $package->price,
            'currency' => 'KWD',
            'customer_initiated' => true,
            'threeDSecure' => false,
            'save_card' => false,
            'metadata' => [
                'transaction_id' => $transaction->id,
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
