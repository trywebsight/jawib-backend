<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\StoreOrderStatusEnum;
use App\Enums\TapPaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Store\StoreOrder;
use App\Models\Store\StoreOrderItem;
use App\Models\Store\StoreProduct;
use App\Services\CouponService;
use App\Services\TapPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StoreOrderController extends Controller
{
    public function myOrders()
    {
        $user = auth('sanctum')->user();
        return $this->success($user->store_orders, __('user store orders'));
    }

    // Display the specified order (must be owned by the authenticated user)
    public function show($id)
    {
        $user = auth('sanctum')->user();
        $order = StoreOrder::with('items.product')->find($id);
        if (!$order) {
            return $this->error([], __("invalid order id"), 422);
        }

        if ($order->user_id !== $user->id) {
            return $this->error([], __('unauthorized'), 403);
        }
        return $this->success($order, __('order retrieved successfully'));
    }


    // Store a newly created order
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.id' => 'required|exists:store_products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), __("validation failed"));
        }

        // return $validator->validated();

        try {
            $user = auth('sanctum')->user();
            $products = $request->input('products');

            $order = StoreOrder::create([
                'user_id' => $user->id,
                'total' => 0,
                'shipping' => 0, // Adjust shipping if needed
                'status' => StoreOrderStatusEnum::PENDING->value,
                'payment_status' => TapPaymentStatusEnum::INITIATED->value,
            ]);

            $totalAmount = 0;
            foreach ($products as $productData) {
                $product = StoreProduct::findOrFail($productData['id']);
                $quantity = $productData['quantity'];

                StoreOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);

                $totalAmount += $product->price * $quantity;
            }

            $order->update([
                'total' => $totalAmount,
            ]);

            // Initiate payment with TapPaymentService
            $tap = TapPaymentService::createCharge($this->paymentData($order));

            $order->tap_id = $tap['id'] ?? null;
            $order->save();

            return $this->success([
                'order_id' => $order->id,
                'payment_link' => $tap['transaction']['url'] ?? null,
            ], __('order created successfully'));
        } catch (\Throwable $e) {
            Log::error("Failed to create store order", ['error' => $e->getMessage()]);
            return $this->error(['error' => $e->getMessage()], __('failed to create order'), 500);
        }
    }


    // Handle payment callback
    public function callback(Request $request)
    {
        if (!$request->tap_id) {
            return $this->error([], __('invalid request id'), 400);
        }

        $tap_response = TapPaymentService::retrieveCharge($request->tap_id);

        $orderId = $tap_response['metadata']['order_id'] ?? null;
        $order = StoreOrder::find($orderId);

        if (!$order) {
            return $this->error([], __('invalid order id'), 400);
        }

        // if ($order->payment_status === TapPaymentStatusEnum::CAPTURED->value) {
        //     return $this->success([], __('payment already processed'));
        // }

        // if Paid successfully
        if (isset($tap_response['status']) && $tap_response['status'] === TapPaymentStatusEnum::CAPTURED->value) {
            $order->update([
                'tap_id'            => $tap_response['id'] ?? $order->tap_id,
                'payment_status'    => $tap_response['status'] ?? $order->payment_status,
                'status'            => StoreOrderStatusEnum::PROCESSING->value,
            ]);
            return $this->success([], __('payment successful'));
        }

        return $this->error([], __('payment failed'), 400);
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
                    'country_code' => '965', // Adjust country code as needed
                    'number' => '51234567', // Ensure you have a valid phone number
                ],
            ],
            'source' => [
                'id' => 'src_all',
            ],
            'redirect' => [
                'url' => route('store.orders.callback'),
            ],
        ];
    }
}
