<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilPriceTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'OrgId' => 'RD-RaiaDrogasil-SA',
            'ShipmentId' => $this->shipment_id,
            'CarrierId' => 'LETS',
            'SourceType' => 'API',
            'ReceivedTimeZone' => "Brazil/East",
            'TimeZone' => "Brazil/East",
            'TrackingReasonCodeId' => '40',
            'MessageType' => 'Preço Entrega',
            'MessageName' => 'Preço Entrega',
            'MessageComments' => (string) formatted_value($this->price),
            'TrackingType' => "SHIPMENT",
            'TrackingReference' => $this->shipment_id,
            'TransportationOrderId' => $this->shipment_id,
            'Extended' => [
                'Pedido' => $this->shipment_id
            ]
        ];
    }
}
