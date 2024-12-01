<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
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

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'do');
                $validatedData['avatar'] = $avatarPath;
            }

            $user->update($validatedData);

            return $this->success($user, __('updated successfully'));
        } catch (\Throwable $th) {
            return $this->error(['errors' => [$th->getMessage()]], __('error, try again later'));
        }
    }
}
