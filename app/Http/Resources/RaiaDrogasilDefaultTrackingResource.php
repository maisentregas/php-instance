<?php

namespace App\Http\Resources;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilDefaultTrackingResource extends JsonResource
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
            'CarrierId' => $this->carrier_id,
            'SourceType' => 'API',
            'ReceivedTimeStamp' => Carbon::parse($this->timestamp)->setTimezone("UTC"),
            'ReceivedTimeZone' => "Brazil/East",
            'TimeZone' => "Brazil/East",
            'StopSeq' => $this->stop_sequence,
            'TrackingReasonCodeId' => $this->tracking_reason_code_id,
            'MessageType' => $this->message_type,
            'MessageName' => $this->message_type,
            'MessageComments' => "",
            'TrackingType' =>  "SHIPMENT",
            'Latitude' => '',
            'Longitude' => '',
            'TrackingEventTimeStamp' => Carbon::parse($this->timestamp)->setTimezone("UTC"),
            'Address1' => '',
            'Address2' => '',
            'StateId' => '',
            'City' => '',
            'PostalCode' => '',
            'CountryId' => 'BRASIL',
            'TrackingReference' => $this->shipment_id,
            'TransportationOrderId' => $this->shipment_id,
            'Extended' => [
                'Pedido' => $this->shipment_id
            ]
        ];
    }
}
