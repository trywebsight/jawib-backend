<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return $this->error([], __('not authorized'), 403);
        }
        return $this->success($user);
    }

    // update account
    public function updateAccount(Request $request)
    {
        try {
            $user = $request->user('sanctum');
            $validator = Validator::make($request->all(), [
                'name'          => 'sometimes|string|max:255',
                'country_code'  => 'sometimes|integer|digits_between:1,4',
                'phone'         => 'sometimes|string|max:255|unique:users,phone,' . $user->id,
                'email'         => 'sometimes|email|max:255|unique:users,email,' . $user->id,
                'password'      => 'sometimes|string',
                'country'       => 'sometimes|nullable|string',
                'bod'           => 'sometimes|nullable|date',
                'gender'        => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->error(['errors' => $validator->errors()], $validator->errors()->first(), 422);
            }

            // Get the validated data
            $validatedData = $validator->validated();

            // Hash the password if it's present in the request
            if (isset($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            }

            // Update the user's information
            $user->update($validatedData);

            return $this->success($user, __('updated successfully'));
        } catch (\Throwable $th) {
            return $this->error(['errors' => [$th->getMessage()]], __('error, try again later'));
        }
    }
}
