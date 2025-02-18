<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{
    AddGeolocationRequest,
    CancelOrFinalizeInsuranceRequest,
    FinalizeInsuranceRequest,
    InsurePersonRequest,
    ListPeriodsByDocumentRequest,
};
use App\Services\LifeInsuranceService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Exception;

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
            $response = $this->lifeInsuranceService->insurePerson((object) $validatedFields['person']);

            if (! $response) {
                return response()->json([
                    'success' => false
                ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
            }

            return response()->json([
                'success' => true,
                'data' => $response
            ], JsonResponse::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addGeolocation(AddGeolocationRequest $addGeolocationRequest)
    {
        try {
            $validatedFields = $addGeolocationRequest->validated();
            $this->lifeInsuranceService->addGeolocation(...$validatedFields);

            return response()->json([
                'success' => true
            ], JsonResponse::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function finalizeInsurance(FinalizeInsuranceRequest $finalizeInsuranceRequest)
    {
        try {
            $validatedFields = $finalizeInsuranceRequest->validated();
            $response = $this->lifeInsuranceService->finalizeInsurance($validatedFields['period_id']);

            return response()->json([
                'success' => true,
                'data' => $response
            ], JsonResponse::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cancelOrFinalizeInsurance(CancelOrFinalizeInsuranceRequest $cancelOrFinalizeInsuranceRequest)
    {
        try {
            $validatedFields = $cancelOrFinalizeInsuranceRequest->validated();
            $response = $this->lifeInsuranceService->cancelOrFinalizeInsurance($validatedFields['period_id'], $validatedFields['period_started_at']);

            return response()->json([
                'success' => true,
                'data' => $response
            ], JsonResponse::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function listPeriodsByDocument(ListPeriodsByDocumentRequest $listPeriodsByDocumentRequest)
    {
        # UNDER CONSTRUCTION
    }
}
