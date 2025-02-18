<?php

namespace App\Services;

use App\Repositories\LifeInsuranceRepository;

use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use Exception;
use Throwable;

class LifeInsuranceService
{
    private $lifeInsuranceRepository;

    public function __construct()
    {
        $this->lifeInsuranceRepository = new LifeInsuranceRepository();
    }

    private function createPerson($person)
    {
        $response = $this->lifeInsuranceRepository->createPerson(
            $person->document,
            $person->first_name,
            $person->last_name,
            $person->birthday,
            $this->areNotFoundResponse($this->personDetailsByEmail($person->email)) ? $person->email : "",
            (isset($person->phone) && $person->phone != "" && $this->areNotFoundResponse($this->personDetailsByCellPhone($person->phone))) ? $person->phone : ""
        );

        if ($response->failed())
            throw new Exception($response->body(), $response->status());

        return $response;
    }

    private function createPersonV2($person)
    {
        $response = $this->lifeInsuranceRepository->createPerson(
            $person->document,
            $person->first_name,
            $person->last_name,
            $person->birthday,
            $this->areNotFoundResponse($this->personDetailsByEmail($person->email)) ? $person->email : "",
            (isset($person->phone) && $person->phone != "" && $this->areNotFoundResponse($this->personDetailsByCellPhone($person->phone))) ? $person->phone : ""
        );

        if ($response->failed())
            throw new Exception($response->body(), $response->status());

        return $response;
    }

    public function personDetailsByDocument($document)
    {
        return $this->lifeInsuranceRepository->personDetailsByDocument($document);
    }

    public function personDetailsByEmail($email)
    {
        return $this->lifeInsuranceRepository->personDetailsByEmail($email);
    }

    public function personDetailsByCellPhone($phone)
    {
        return $this->lifeInsuranceRepository->personDetailsByCellPhone($phone);
    }

    public function addGeolocation($document, $latitude, $longitude, $datetime)
    {
        return $this->lifeInsuranceRepository->addGeolocation($document, $latitude, $longitude, $datetime);
    }

    public function addGeolocationV2($document, $latitude, $longitude, $requestId)
    {
        // UNDER CONSTRUCTION
    }

    private function createContract($document)
    {
        $response = $this->lifeInsuranceRepository->createContract($document);

        if ($response->failed())
            throw new Exception($response->body(), $response->status());

        return json_decode($response->body())->id;
    }

    private function createPeriod($document, $startedAt)
    {
        return $this->lifeInsuranceRepository->createPeriod($document, $startedAt);
    }

    private function updatePeriod($periodId, $finishedAt)
    {
        return $this->lifeInsuranceRepository->updatePeriod($periodId, $finishedAt);
    }

    private function cancelPeriod($periodId, $finishedAt)
    {
        return $this->lifeInsuranceRepository->cancelPeriod($periodId, $finishedAt);
    }

    public function insurePerson($person): Collection|bool
    {
        try {
            $activeContract = null;
            $startedAt = date('Y-m-d\TH:i:s');

            $createPeriodResponse = $this->createPeriod($person->document, $startedAt);

            if ($this->areNotFoundResponse($createPeriodResponse)) {
                $personDetailResponse = $this->personDetailsByDocument($person->document);

                if ($this->areNotFoundResponse($personDetailResponse))
                    $this->createPerson($person);
                else
                    $activeContract = $this->hasActiveContract($person, $personDetailResponse);

                if (! $activeContract)
                    $activeContract = $this->createContract($person->document);

                $createPeriodResponse = $this->createPeriod($person, $startedAt);
            }

            $periodId = json_decode($createPeriodResponse->body())->id;

            return collect([
                'period_id' => $periodId,
                'started_at' => $startedAt,
                'person' => $person
            ]);
        } catch (Exception $exception) {
            Log::error('Life insurance | ' . $exception);

            return false;
        }
    }

    public function finalizeInsurance($periodId): Collection
    {
        try {
            $finishedAt = date('Y-m-d\TH:i:s');

            Log::info('Finalizing insurance with period id. ' . $periodId);

            $response = $this->updatePeriod($periodId, $finishedAt);

            # Conflict means that period is already updated and finalized
            if ($response->successful() || $this->areConflictResponse($response)) {
                return collect([
                    'status' => 'finished',
                    'period_id' => $periodId,
                    'finished_at' => $finishedAt
                ]);
            }

            throw new Exception($response->body(), $response->status());
        } catch (Exception $exception) {
            Log::error('Life insurance with period id. ' . $periodId . ' | ' . $exception);

            throw $exception;
        }
    }

    public function cancelOrFinalizeInsurance($periodId, $periodStartedAt): Collection
    {
        try {
            $finishedAt = date('Y-m-d\TH:i:s');
            $status = '';

            Log::info('Finalizing or canceling insurance with period id. ' . $periodId);

            if ($this->cancelAreNotOutOfTime($periodStartedAt)) {
                $response = $this->cancelPeriod($periodId, $finishedAt);
                $status = 'canceled';
            } else {
                $response = $this->updatePeriod($periodId, $finishedAt);
                $status = 'finished';
            }

            # Conflict means that period is already updated and finalized
            if ($response->successful() || $this->areConflictResponse($response)) {
                return collect([
                    'status' => $status,
                    'period_id' => $periodId,
                    'period_started_at' => $periodStartedAt,
                    'finished_at' => $finishedAt
                ]);
            }

            throw new Exception($response->body(), $response->status());
        } catch (Exception $exception) {
            Log::error('Life insurance | ' . $exception);

            throw $exception;
        }
    }

    private function cancelAreNotOutOfTime($periodStartedAt): bool
    {
        $now = new Carbon();
        $startedAt = new Carbon($periodStartedAt);
        $expirationTimeInSeconds = 110;

        return $startedAt->diffInSeconds($now) <= $expirationTimeInSeconds;
    }

    private function hasActiveContract($document, $response = null): bool
    {
        try {
            if (is_null($response))
                $response = $this->personDetailsByDocument($document);

            if ($this->areServerErrorResponse($response))
                throw new Exception($response->body(), $response->status());

            $contracts = json_decode($response->body())[0]->contracts;

            foreach ($contracts as $contract) {
                if ($contract->id != null)
                    return true;
            }

            return false;
        } catch (Exception $exception) {
            Log::error('Life insurance | ' . $exception);

            throw $exception;
        }
    }

    private function areConflictResponse($response): bool
    {
        return $response->status() === 409;
    }

    private function areServerErrorResponse($response): bool
    {
        return $response->status() >= 500;
    }

    private function areNotFoundResponse($response): bool
    {
        return $response->status() === 404;
    }
}
