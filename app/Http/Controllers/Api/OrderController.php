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

    public function myOrders()
    {
        $user = auth('sanctum')->user();
        return $this->success($user->orders, __('user orders'));
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon'            => 'nullable|sometimes|string|exists:coupons,code',
            'package_id'        => 'required|exists:packages,id',
            'payment_method'    => 'required|in:src_kw.knet,src_card,src_all',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), __("validation failed"));
        }

        try {
            $user = auth('sanctum')->user();
            $package_id = $request->input('package_id');
            $couponCode = $request->input('coupon');
            $payment_method = $request->input('payment_method');
            $coupon = null;

            if ($couponCode) {
                $coupon = $this->couponService->getCouponByCode($couponCode);
                if (!$coupon) {
                    return $this->error(['coupon' => __('invalid or expired coupon')], __('invalid coupon.'));
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'payment_status' => TapPaymentStatusEnum::INITIATED->value,
                'coupon' => $couponCode,
            ]);

            $package = Package::findOrFail($package_id);
            OrderPackage::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'quantity' => 1,
                'price' => $package->price,
            ]);
            $totalAmount = $package->price;

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

            $tap = TapPaymentService::createCharge($this->paymentData($order, $payment_method));

            $order->tap_id = $tap['id'];
            $order->save();

            return $this->success(['order_id' => $order->id, 'payment_link' => $tap['transaction']['url'] ?? null]);
            return $this->success($order->load('packages'));
        } catch (\Throwable $e) {
            Log::debug("Failed order", [$e->getMessage()]);
            return $this->error(['errors' => [$e->getMessage()]], __('failed to create order. please try again.'));
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
            return redirect()->route('orders.tap_payment_status', ['status' => 'success']);
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

            return redirect()->route('orders.tap_payment_status', ['status' => 'success']);
            return $this->success([], __('paid successfully'));
        }
        return redirect()->route('orders.tap_payment_status', ['status' => 'failed']);
        return $this->error([], __("payment failed"));
    }

    public function payment_status(Request $request)
    {
        // Retrieve the payment status parameter from the URL
        $paymentStatus = $request->query('status');

        if ($paymentStatus == 'success') {
            return $this->success([], __('order paid successfully!'), 201);
        } else {
            return $this->error([], __('payment was not successful'), 400);
        }
    }


    private function paymentData($order, $payment_method = 'src_all')
    {
        $user = $order->user;
        if (!in_array($payment_method, ['src_kw.knet', 'src_card', 'src_all'])) {
            $payment_method = 'src_all';
        }

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
                'id' => $payment_method,
            ],
            'redirect' => [
                'url' => route('orders.tap_callback'),
            ],
        ];
    }
}
