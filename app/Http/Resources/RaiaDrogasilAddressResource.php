<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $matches = [];
        preg_match('/\d+$/', $this->Address1, $matches);

        return [
            'street' => $this->Address1,
            'number' => $matches,
            'district' => $this->Address3,
            'complement' => $this->Address2,
            'city' => $this->City,
            'state' => $this->State,
            'postal_code' => $this->PostalCode,
            'phone' => $this->Phone,
            'latitude' => $this->Latitude,
            'longitude' => $this->Longitude
        ];
    }
}
