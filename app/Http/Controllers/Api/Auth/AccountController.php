<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return $this->error([], __('not authorized'), 403);
        }
        // $user->games_count = $user->games?->count();
        return $this->success((new UserResource($user)));
    }

    // update account
    public function updateAccount(Request $request)
    {
        try {
            $user = $request->user('sanctum');
            if (!$user) {
                return $this->error([], __('not authorized'), 403);
            }
            $validator = Validator::make($request->all(), [
                'name'          => 'sometimes|string|max:255',
                'email'         => 'sometimes|email|max:255|unique:users,email,' . $user->id,
                'country'       => 'sometimes|nullable|string',
                'dob'           => 'sometimes|nullable|date',
                'gender'        => 'sometimes|nullable|string',
                'avatar'        => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->error(['errors' => $validator->errors()], $validator->errors()->first(), 422);
            }

            $validatedData = $validator->validated();

            if (isset($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            }

            if ($request->avatar) {
                // Remove data:image/jpeg;base64, from the base64 string
                $image_parts = explode(";base64,", $request->avatar);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);

                // Generate unique filename
                $filename = uniqid() . '.' . $image_type;

                // Store the file
                $path = 'avatars/' . $filename;
                Storage::disk('do')->put($path, $image_base64);

                $validatedData['avatar'] = $path;
            }

            $user->update($validatedData);

            return $this->success($user, __('updated successfully'));
        } catch (\Throwable $th) {
            return $this->error(['errors' => [$th->getMessage()]], __('error, try again later'));
        }
    }

    // change password
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user('sanctum');
            if (!$user) {
                return $this->error([], __('not authorized'), 403);
            }
            $validator = Validator::make($request->all(), [
                'current_password'  => 'required|string|min:6',
                'new_password'      => [
                    'required',
                    'string',
                    'min:6',
                    'confirmed',
                    // function ($attribute, $value, $fail) use ($user) {
                    //     if (Hash::check($value, $user->password)) {
                    //         $fail(__('new password can\'t be same as current password'));
                    //     }
                    // },
                ],
            ]);

            if ($validator->fails()) {
                return $this->error(['errors' => $validator->errors()], $validator->errors()->first(), 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error(['errors' => ['current_password' => [__('current password is not correct')]]], __('current password is not correct'), 422);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return $this->success($user, __('password updated successfully'));
        } catch (\Throwable $th) {
            return $this->error(['errors' => [$th->getMessage()]], __('error, try again later'));
        }
    }
}
