<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'name' => $this->name,
          'surname' => $this->surname,
          'email' => $this->email,
          'email_verified_at' => $this->email_verified_at,
          'birth_date' => $this->birth_date,
          'address' => $this->address,
          'phone' => $this->phone,
          'dni' => $this->dni,
          'user_type_id' => $this->user_type_id,
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at
        ];
    }
}
