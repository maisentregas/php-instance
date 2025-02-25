<?php

namespace App\Services;

use App\Http\Resources\{
    RaiaDrogasilAddressResource,
    RaiaDrogasilCreateOrderResource,
    RaiaDrogasilDefaultTrackingResource,
    RaiaDrogasilIntegratedTrackingResource,
    RaiaDrogasilPriceTrackingResource,
    RaiaDrogasilRecallOrderResource,
    RaiaDrogasilTenderAcceptedResource,
    RaiaDrogasilTenderRejectedResource
};

use Carbon\Carbon;
use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Exception;

class RaiaDrogasilService
{
    private $pubSubClient;
    private $url = 'https://api.maisentregas.com';

    public function __construct()
    {
        putenv(self::getEnv());

        $this->pubSubClient = new PubSubClient([
            'projectId' => self::getProjectId(),
        ]);
    }

    private function getEnv()
    {
        return 'GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/private/credentials/raia-drogasil/google_credentials.json');
    }

    private function getProjectId()
    {
        $json = File::get(storage_path('app/private/credentials/raia-drogasil/google_credentials.json'));
        $data = json_decode($json, true);

        return $data['project_id'];
    }

    private function token($email, $apiKey)
    {
        if (! cache()->store('redis')->has('token-' . $email . '-' . hash('sha256', $apiKey))) {
            $params = [
                'email' => $email,
                'apikey' => $apiKey
            ];

            $uri = '/auth';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-App-ID' => 'integration'
            ])->post($this->url.$uri, $params);

            if ($response->failed())
                throw new Exception($response->body(), $response->status());

            # Get expire from response maybe
            $seconds = 60 * 60; # 3600 seconds | 60 minutes

            cache()->store('redis')->put('token-' . $email . '-' . hash('sha256', $apiKey), $response['access_token'], $seconds);
        }

        return cache()->store('redis')->get('token-' . $email . '-' . hash('sha256', $apiKey));
    }

    private function getApiKey($cnpj)
    {
        $uri = '/companyRaiaDrogasil/' . $cnpj . '/apiKey';

        # Enviar token de segurança.
        $apiKeyResponse = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-App-ID' => 'integration',
            'X-Raia-Drogasil-Token' => config('services.raia_drogasil.internal_access_token')
        ])->get($this->url.$uri);

        if ($apiKeyResponse->failed())
            throw new Exception($apiKeyResponse->body(), $apiKeyResponse->status());

        return $apiKeyResponse['apiKey'];
    }

    public function handleMessages()
    {
        $subscription = $this->pubSubClient->subscription('RAIA-Outbound-Tender-LETS');
        $pulledMessages = $subscription->pull();
        
        foreach ($pulledMessages as $pulledMessage) {
            $orderMessage = json_decode($pulledMessage->data(), true);

            try {
                $apiKey = $this->getApiKey($orderMessage['Extended']['CNPJLoja']);
                $accessToken = $this->token($orderMessage['Extended']['Email'], $apiKey);

                if (isset($orderMessage['TenderResponseStatus']) && strtolower($orderMessage['TenderResponseStatus']) == "recalled") {
                    $this->recallOrder($orderMessage, $accessToken);
                } else {
                    $this->createOrder($orderMessage, $accessToken);
                }
            } catch (Exception $exception) {
                Log::error('Error while messages were being processed | ' . $exception->getMessage());
            }

            $subscription->acknowledge($pulledMessage);
        }
    }

    private function createOrder($orderMessage, $accessToken)
    {
        # If the JSON structure isn't supported
        if (isset($request['Pedido']))
            return false;

        $existsOrderResponse = Http::asForm()->withHeaders([
            'X-App-ID' => 'integration',
            'x-access-token' => $accessToken
        ])->get($this->url . '/order/' . $orderMessage['ShipmentId'] . '/pedido');

        if ($existsOrderResponse->failed()) {
            # Order identification data
            $data['carrier_id'] = $orderMessage['CarrierId'];
            $data['shipment_id'] = $orderMessage['ShipmentId'];
            $data['shipper_id'] = $orderMessage['ShipperId'];
            $data['order_id'] = $orderMessage['Extended']['Pedido'];
            $data['document'] = $orderMessage['Extended']['CNPJLoja'];
            $data['transportation_order_id'] = $orderMessage['Stop'][0]['StopTransportationOrder'][0]['TransportationOrderId'];

            $data['email'] = $orderMessage['Extended']['Email'];

            try {
                # Order type data
                $data['payment'] = 'FATURADO';
                $data['billing'] = 'ENTREGA';
                $data['delivery'] = 'IMEDIATO';

                # Address data
                $data['address'] = array_map(fn ($stop) => new RaiaDrogasilAddressResource((object) array_merge(
                    $stop['FacilityAddress'],
                    ['Latitude' => $stop['Latitude'], 'Longitude' => $stop['Longitude']]
                )), $orderMessage['Stop']);

                $createOrderResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-App-ID' => 'integration',
                    'x-access-token' => $accessToken
                ])->post($this->url . '/order/confirm', new RaiaDrogasilCreateOrderResource((object) $data));

                if ($createOrderResponse->failed() || ($createOrderResponse->successful() && $createOrderResponse['success'] == false))
                    throw new Exception($createOrderResponse->body(), $createOrderResponse->status());

                Log::info('Order created. | Id. ' . $createOrderResponse['id'] . ' |  Shipment id. ' . $data['shipment_id']);

                $data['created_order_id'] = $createOrderResponse['id'];
                $data['price'] = $createOrderResponse['billing']['value'];

                $data['reason_code'] = '1';
                $data['reason_message'] = 'Integrado';

                try {
                    $this->publishMessage(new RaiaDrogasilTenderAcceptedResource((object) $data));

                    $this->publishTracking(new RaiaDrogasilIntegratedTrackingResource((object) $data));
                    $this->publishTracking(new RaiaDrogasilPriceTrackingResource((object) $data));
                    
                    Log::info('Response sent. | Id. ' . $createOrderResponse['id'] . ' |  Shipment id. ' . $data['shipment_id']);
                } catch (Exception $exception) {
                    Log::error('Error while trying to send response. | Id. ' . $createOrderResponse['id'] . ' | Shipment id. ' . $data['shipment_id'] . ' | Exceção ' . $exception->getMessage() . ' | ' . $exception->getTraceAsString());
                }
            } catch (Exception $exception) {
                Log::error('Error while trying to create order. | ' . $exception->getMessage() . ' | ' . $exception->getTraceAsString());

                $data['reason_code'] = '36';
                $data['reason_message'] = 'Rejeitado pela Transportadora';

                $this->publishMessage(new RaiaDrogasilTenderRejectedResource((object) $data));
            }
        } else {
            Log::info('Order with shipment id. ' . $orderMessage['ShipmentId'] . ' already exists.');

            return false;
        }
    }

    private function recallOrder($orderMessage, $accessToken)
    {
        $orderResponse = Http::asForm()->withHeaders([
            'X-App-ID' => 'integration',
            'x-access-token' => $accessToken
        ])->get($this->url . '/order/' . $orderMessage['ShipmentId'] . '/pedido');

        if ($orderResponse->failed()) {
            Log::info('Order with shipment id. ' . $orderMessage['ShipmentId'] . ' not found.');

            return false;
        }

        $timestamp = Carbon::now();

        $data = [
            'reason' =>  "Cancelado pelo cliente",
            'timestamp' => $timestamp,
            'shipment_id' => $orderMessage['ShipmentId'],
            'carrier_id' => $orderMessage['CarrierId'],
            'latest_tracked_stop_sequence' => $orderMessage['LatestTrackedStopSequence']
        ];

        $canceledOrderResponse = Http::asForm()->withHeaders([
            'X-App-ID' => 'integration',
            'x-access-token' => $accessToken
        ])->post($this->url . '/order/' . $orderResponse['id'] . '/cancel', [
            'reason' => $data['reason']
        ]);

        if ($canceledOrderResponse->successful()) {
            $this->publishTracking(new RaiaDrogasilRecallOrderResource((object) $data));

            Log::info('Order cancelled. | Id. ' . $orderResponse['id'] . ' | Shipment id. ' . $orderMessage['ShipmentId']);
        }
    }

    public function sendTracking(Array $params)
    {
        # Integration name == 'raia'?

        $data = [
            'shipment_id' => $params['shipment_id'],
            'carrier_id' => $params['carrier_id'],
            'stop_sequence' => $params['stop_sequence'],
            'tracking_reason_code_id' => $params['tracking_code'],
            'message_type' => $params['message_type'],
            'message_name' => $params['message_name'] ?? $params['message_type'],
            'timestamp' => Carbon::now()
        ];

        try {
            $this->publishTracking(new RaiaDrogasilDefaultTrackingResource((object) $data));

            Log::info('Tracking with shipment id. ' . $params['shipment_id'] . ' was sent at ' . $data['timestamp']);
        } catch (\Throwable $th) {
            Log::info('Error while triyng to send tracking with shipment id. ' . $params['shipment_id'] . ' at ' . $data['timestamp']);

            throw $th;
        }

        return $data;
    }

    private function publishTracking($message)
    {
        Log::info('Tracking message pushed to Google Pub/Sub | ' . json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $topic = $this->pubSubClient->topic('RAIA-Inbound-CarrierTRKMSG');
        $topic->publish((new MessageBuilder())->setData(json_encode($message))->build());
    }

    private function publishMessage($message)
    {
        Log::info('Default message pushed to Google Pub/Sub | ' . json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $topic = $this->pubSubClient->topic('RAIA-Inbound-TenderResponse');
        $topic->publish((new MessageBuilder())->setData(json_encode($message))->build());
    }
}
