<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);
        // If validation fails, return an error response
        if ($validator->fails()) {
            return $this->error($validator->errors(), __("validation failed"));
        }
        $couponService = new CouponService;
        $coupon = $couponService->getCouponByCode($request->code);

        if (!$coupon) {
            return $this->error([], __('invalid or expired coupon'));
        }
        $res = [
            'id' =>  1,
            'code' => $coupon->code,
            'discount_type' =>  $coupon->discount_type,
            'discount_value' =>  $coupon->discount_value,
        ];
        return $this->success($res, __('valid coupon'));
    }
}
