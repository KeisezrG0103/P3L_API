<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class resource_login extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    private $token;
    private $role;

    public function __construct($resource, $token, $role = null)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->role = $role;
    }



    public function toArray(Request $request): array
    {
        if($this->role == null){
            return [
                'Nama' => $this->Nama,
                'Email' => $this->Email,
                'token' => $this->token
            ];
        } else {
            return [
                'Nama' => $this->Nama,
                'token' => $this->token,
                'role' => $this->role
            ];
        }

    }


}
