<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrFinalizeInsuranceRequest;
use App\Http\Requests\FinalizeInsuranceRequest;
use App\Http\Requests\InsurePersonRequest;
use App\Services\LifeInsuranceService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LifeInsuranceController extends Controller
{
    private $lifeInsuranceService;

    public function __construct(LifeInsuranceService $lifeInsuranceService)
    {
        $this->lifeInsuranceService = $lifeInsuranceService;
    }

    public function insurePerson(InsurePersonRequest $insurePersonRequest)
    {
        try {
            $validatedFields = $insurePersonRequest->validated();
            $response = $this->lifeInsuranceService->insurePerson($validatedFields['person']);

            return response()->json([
                '' => ''
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                '' => ''
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function finalizeInsurance(FinalizeInsuranceRequest $finalizeInsuranceRequest)
    {
        try {
            $validatedFields = $finalizeInsuranceRequest->validated();
            $response = $this->lifeInsuranceService->finalizeInsurance($validatedFields['period_id']);

            return response()->json([
                '' => ''
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                '' => ''
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cancelOrFinalizeInsurance(CancelOrFinalizeInsuranceRequest $cancelOrFinalizeInsuranceRequest)
    {
        try {
            $validatedFields = $cancelOrFinalizeInsuranceRequest->validated();
            $response = $this->lifeInsuranceService->cancelOrFinalizeInsurance($validatedFields['period_id'], $validatedFields['period_started_at']);

            return response()->json([
                '' => ''
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                '' => ''
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
