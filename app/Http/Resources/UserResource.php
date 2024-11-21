<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'username'              => $this->username,
            'email'                 => $this->email,
            'avatar'                => media_url($this->avatar),
            'country_code'          => $this->country_code,
            'phone'                 => $this->phone,
            'points'                => $this->points,
            'rank'                  => $this->rank,
            'dob'                   => $this->dob,
            'country'               => $this->country,
            'phone_verified'        => $this->phone_verified,
            'balance'               => (int) $this->balance,
            'games_count'           => $this->games?->count(),
        ];
    }
}
