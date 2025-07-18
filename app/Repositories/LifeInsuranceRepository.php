<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use Exception;

class LifeInsuranceRepository
{
	private $apiUrl;
	private $secretKey;
    private $userKey;

	public function __construct()
	{
        $this->apiUrl = config('services.mais_entregas.iza_intermittent.api_url');
	}

    public function setCredentials($userKey = null, $secretKey = null)
    {
        $this->secretKey = $secretKey ?? config('services.mais_entregas.iza_intermittent.user_secret');
        $this->userKey = $userKey ?? config('services.mais_entregas.iza_intermittent.user_key');

        return;
    }

    public function createPerson($document, $firstName, $lastName, $birthday, $email, $phone)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document),
            "name" => $firstName . ' ' . $lastName,
            "birthed_at" => Carbon::parse(str_replace('/', '-', $birthday))->format('Y-m-d'),
            "email" => $email,
            "main_cell_phone" => str_replace([' ', '.', '-', '(', ')', '+'], '', $phone)
        ];

        $specificUrl = "/integrations/persons";

        return $this->postHttp($specificUrl, $params);
    }

    public function createPersonV2($document, $firstName, $lastName, $birthday, $email, $phone)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document),
            "name" => $firstName . ' ' . $lastName,
            "birthed_at" => Carbon::parse(str_replace('/', '-', $birthday))->format('Y-m-d'),
            "email" => $email,
            "main_cell_phone" => str_replace([' ', '.', '-', '(', ')', '+'], '', $phone)
        ];

        $specificUrl = "/integrations/v2/persons";

        return $this->postHttp($specificUrl, $params);
    }

    public function personDetailsByDocument($document)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document)
        ];

        $specificUrl = "/integrations/persons";
        
        $response = $this->getHttp($specificUrl, $params);
        
        if ($response->failed())
            Log::info('Life insurance personDetailsByDocument failed response: ' . $response->body());

        return $response;
    }

    public function personDetailsByEmail($email)
    {
        $params = [
            "email" => $email
        ];

        $specificUrl = "/integrations/persons";

        $response = $this->getHttp($specificUrl, $params);

        if ($response->failed())
            Log::info('Life insurance personDetailsByEmail failed response: ' . $response->body());

        return $response;
    }

    public function personDetailsByCellPhone($phone)
    {
        $params = [
            "main_cell_phone" => str_replace([' ', '.', '-', '(', ')', '+'], '', $phone)
        ];

        $specificUrl = "/integrations/persons";

        $response = $this->getHttp($specificUrl, $params);

        if ($response->failed())
            Log::info('Life insurance personDetailsByCellPhone failed response: ' . $response->body());

        return $response;
    }

    public function addGeolocation($document, $datetime, $latitude, $longitude)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document),
            "datetime" => $datetime,
            "lat" => strval(round($latitude, 6)),
            "long" => strval(round($longitude, 6))
        ];

        $specificUrl = "/integrations/intermittent/persons/geolocation";

        $response = $this->postHttp($specificUrl, $params);

        if ($response->failed())
            Log::info('Life insurance addGeolocation failed response: ' . $response->body());

        return $response;
    }

    public function createContract($document)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document)
        ];

        $specificUrl = "/integrations/contracts";

        return $this->postHttp($specificUrl, $params);
    }

    public function partnerInfo()
    {
        $params = [];

        $specificUrl = "/integrations/partner-info";

        return $this->getHttp($specificUrl, $params);
    }

    public function createPeriod($document, $startedAt)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document),
            "started_at" => $startedAt
        ];

        $specificUrl = "/integrations/intermittent/persons/periods";

        return $this->postHttp($specificUrl, $params);
    }

    public function reportPersonAvailability($document)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document),
            "state" => "available"
        ];

        $specificUrl = "/integrations/intermittent/persons/availability";

        return $this->postHttp($specificUrl, $params);
    }

    public function reportPersonUnavailability($document)
    {
        $params = [
            "doc" => str_replace([' ', '.', '-', '(', ')'], '', $document),
            "state" => "unavailable"
        ];

        $specificUrl = "/integrations/intermittent/persons/availability";

        return $this->postHttp($specificUrl, $params);
    }

    public function listPeriodsByPersonDocument($document, $startedAt = null, $finishedAt = null)
    {
        $params = [
            "doc" => str_replace(array(' ', '.', '-', '(', ')'), '', $document),
            "started_at" => $startedAt,
            "finished_at" => $finishedAt
        ];

        $specificUrl = "/integrations/intermittent/persons/periods";

        return $this->getHttp($specificUrl, $params);
    }

    public function updatePeriod($periodId, $finishedAt)
    {
        $params = [
            "finished_at" => $finishedAt
        ];

        $specificUrl = "/integrations/intermittent/persons/periods/" . $periodId;

        $response = $this->putHttp($specificUrl, $params);

        if ($response->failed())
            Log::error('Life insurance updatePeriod failed response: ' . $response->body());

        return $response;
    }

    public function cancelPeriod($periodId, $finishedAt)
    {
        $params = [
            "finished_at" => $finishedAt
        ];

        $specificUrl = "/integrations/intermittent/persons/periods/" . $periodId . "/cancel";

        $response = $this->putHttp($specificUrl, $params);

        if ($response->failed())
            Log::error('Life insurance cancelPeriod failed response: ' . $response->body());

        return $response;
    }

    private function getHttp($specificUrl, $params)
    {
        return Http::withBasicAuth($this->userKey, $this->secretKey)->get($this->apiUrl.$specificUrl, $params);
    }

    private function postHttp($specificUrl, $params)
    {
        return Http::withBasicAuth($this->userKey, $this->secretKey)->post($this->apiUrl.$specificUrl, $params);
    }

    private function putHttp($specificUrl, $params)
    {
        return Http::withBasicAuth($this->userKey, $this->secretKey)->put($this->apiUrl.$specificUrl, $params);
    }
}