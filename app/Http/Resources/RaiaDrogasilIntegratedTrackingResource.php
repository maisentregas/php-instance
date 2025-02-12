<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilIntegratedTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'OrgId' => "RD-RaiaDrogasil-SA",
            'ShipmentId' => $this->shipment_id,
            'CarrierId' => $this->carrier_id,
            'SourceType' => "API",
            'ReceivedTimeStamp' => now()->utc(),
            'ReceivedTimeZone' => "Brazil/East",
            'TimeZone' => "Brazil/East",
            'StopSeq' => 1,
            'TrackingReasonCodeId' => "1",
            'MessageType' => "Integrado",
            'MessageName' => "Integrado",
            'MessageComments' => "Integrado",
            'TrackingType' => "SHIPMENT",
            'Latitude' => "",
            'Longitude' => "",
            'Address1' => "",
            'Address2' => "",
            'StateId' => $this->address[0]->State,
            'City' => $this->address[0]->City,
            'PostalCode' => $this->address[0]->PostalCode,
            'CountryId' => "BRASIL",
            'TrackingReference' => $this->shipment_id,
            'TransportationOrderId' => $this->transportation_order_id,
            'Extended' => [
                'Pedido' => $this->order_id
            ]
        ];
    }
}
