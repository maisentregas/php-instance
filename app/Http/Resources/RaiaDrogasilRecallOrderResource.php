<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilRecallOrderResource extends JsonResource
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
            'CarrierId' => "LETS",
            'SourceType' => "API",
            'ReceivedTimeStamp' => Carbon::parse($this->date)->setTimezone("UTC"),
            'ReceivedTimeZon' => "Brazil/East",
            'TimeZone' => "Brazil/East",
            'StopSeq' => $this->Stop[0]['StopSequence'] ?? 1,
            'TrackingReasonCodeId' => "8",
            'MessageType' => "Entrega Cancelada",
            'MessageName' => "Entrega Cancelada",
            'MessageComments' => "Entrega cancelada pelo cliente",
            'TrackingType' => "SHIPMENT",
            'Latitude' => "",
            'Longitude' => "",
            'Address1' => "",
            'Address2' => "",
            'StateId' => "",
            'City' => "",
            'PostalCode' => "",
            'CountryId' => "BRASIL",
            'TrackingReference' => $this->shipment_id,
            'TransportationOrderId' => $this->shipment_id,
            'Extended' => [
                "Pedido" => $this->shipment_id
            ]
        ];
    }
}
