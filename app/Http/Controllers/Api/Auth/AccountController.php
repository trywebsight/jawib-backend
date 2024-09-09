<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class AccountController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();
        return $this->success($user);
    }

    // update account
    public function update_info(Request $request)
    {
        $user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:255|unique:users,phone,' . $user->id,
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->errors()], $validator->errors()->first(), 422);
        }
        $updated = $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);
        if ($updated) {
            return $this->success($user, __('updated successfully'));
        }
        return $this->error(null, __('error, try again later'));
    }
}
