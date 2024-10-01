<?php

namespace App\Http\Controllers\Api;

use App\Enums\TapPaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPackage;
use App\Models\Package;
use App\Services\CouponService;
use App\Services\TapPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon' => 'nullable|sometimes|string|exists:coupons,code',
            'packages' => 'required|array',
            'packages.*.id' => 'required|exists:packages,id',
            'packages.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), __("validation failed"));
        }

        try {
            $user = auth('sanctum')->user();
            $packages = $request->input('packages');
            $couponCode = $request->input('coupon');
            $coupon = null;

            if ($couponCode) {
                $coupon = $this->couponService->getCouponByCode($couponCode);

                if (!$coupon) {
                    return $this->error(['coupon' => 'Invalid or expired coupon.'], __('Invalid coupon.'));
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'payment_status' => TapPaymentStatusEnum::INITIATED->value,
                'coupon' => $couponCode,
            ]);

            $totalAmount = 0;
            foreach ($packages as $packageData) {
                $package = Package::findOrFail($packageData['id']);
                $quantity = $packageData['quantity'];

                OrderPackage::create([
                    'order_id' => $order->id,
                    'package_id' => $package->id,
                    'quantity' => $quantity,
                    'price' => $package->price,
                ]);

                $totalAmount += $package->price * $quantity;
            }

            // Calculate discount
            $discount = 0;
            if ($coupon) {
                $discount = $this->couponService->calculateDiscount($coupon, $totalAmount);
            }

            $finalAmount = $totalAmount - $discount;

            $order->update([
                'total' => $finalAmount,
                'discount' => $discount,
            ]);

            $tap = TapPaymentService::createCharge($this->paymentData($order));

            $order->tap_id = $tap['id'];
            $order->save();

            return $this->success(['order_id' => $order->id, 'payment_link' => $tap['transaction']['url'] ?? null]);
            return $this->success($order->load('packages'));
        } catch (\Throwable $e) {
            Log::debug("Failed order", [$e->getMessage()]);
            return $this->error(['errors' => [$e->getMessage()]], __('Failed to create order. Please try again.'));
        }
    }


    public function callback(Request $request)
    {
        if (!$request->tap_id) {
            return $this->error([], __('invalid request id'));
        }

        $tap_response = TapPaymentService::retrieveCharge($request->tap_id);

        $order = Order::find($tap_response['metadata']['order_id'] ?? null);
        if (!$order) {
            return $this->error([], __('invalid order id'));
        }

        if ($order->payment_status === TapPaymentStatusEnum::CAPTURED->value) {
            return $this->success([], __("payment already processed"));
        }

        $order->update([
            'tap_id' => $tap_response['id'] ?? $order->tap_id,
            'payment_status' => $tap_response['status'] ?? $order->payment_status,
        ]);

        if (isset($tap_response['status']) && $tap_response['status'] == TapPaymentStatusEnum::CAPTURED->value) {
            // If order has a coupon, increment coupon usage
            if ($order->coupon) {
                $coupon = $this->couponService->getCouponByCode($order->coupon);
                if ($coupon) {
                    $this->couponService->incrementUsedTimes($coupon->code);
                    $this->couponService->incrementUserCouponUsage($coupon, $order->user);
                }
            }

            $totalCredits = $order->packages->sum(function ($orderPackage) {
                return $orderPackage->package->games_count * $orderPackage->quantity;
            });

            $order->user->deposit($totalCredits, ['description' => 'package purchase', 'order_id' => $order->id]);
            return $this->success([], __('paid successfully'));
        }
        return $this->error([], __("payment failed"));
    }


    private function paymentData($order)
    {
        $user = $order->user;

        return [
            'amount' => $order->total, // Ensure this is the final amount after discount
            'currency' => 'KWD',
            'customer_initiated' => true,
            'threeDSecure' => false,
            'save_card' => false,
            'metadata' => [
                'order_id' => $order->id,
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
                'url' => route('orders.tap_callback'),
            ],
        ];
    }
}
