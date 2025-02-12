<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaiaDrogasilTenderRejectedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "Metadata" => [
                "RouterMessageType" => "CARRIER_TENDER_RESPONSE",
                "ActionType" => "TENDER_DECLINE",
                "PartnerId" => "raiassf11o:RD-RaiaDrogasil-SA",
                "SenderRouterOrgId" => "mxcassf11o:" . $this->carrier_id,
                "PartnerAliasId" => $this->carrier_id
            ],
            "TenderResponseDTO" => [
                "ShipmentId" => $this->shipment_id,
                "ShipperId" => $this->shipper_id,
                "CarrierId" => $this->carrier_id,
                "ReasonCode" => $this->reason_code,
                "ReasonMessage" => $this->reason_message,
                "TenderResponseStatus" => "DECLINED"
            ]
        ];
    }
}
