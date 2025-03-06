<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilCreateOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'client' => $this->email,
            'city' => $this->address[0]->State . '/' . $this->address[0]->City,
            'payment' => $this->payment,
            'billing' => $this->billing,
            'delivery' => $this->delivery,
            'order' => $this->shipment_id,
            'document' => $this->document,
            'address' => $this->address,
            'raiaDrogasilDeliveryId' => 'raia_drogasil'
        ];
    }
}
