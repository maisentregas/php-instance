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

use Exception;
use Illuminate\Support\Facades\Log;

class LifeInsuranceController extends Controller
{
    private $lifeInsuranceService;

    public function __construct(LifeInsuranceService $lifeInsuranceService)
    {
        $this->lifeInsuranceService = $lifeInsuranceService;
    }

    private function decryptData($encryptedData)
    {
        $key = hex2bin(config('services.mais_entregas.iza_intermittent.crypto_key'));
        $cipher = 'aes-128-cbc';

        list($iv, $encrypted) = explode(':', $encryptedData);

        if (!$iv || !$encrypted) {
            throw new \Exception("Dados criptografados inválidos ou malformados");
        }

        $iv = hex2bin($iv);

        if ($iv === false) {
            throw new \Exception("Falha ao converter IV de hexadecimal para binário");
        }

        $decrypted = openssl_decrypt(hex2bin($encrypted), $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new \Exception("Falha na descriptografia: " . openssl_error_string());
        }

        return $decrypted;
    }

    public function insurePerson(InsurePersonRequest $insurePersonRequest)
    {
        try {
            $validatedFields = $insurePersonRequest->validated();

            $this->lifeInsuranceService->setCredentials(...explode(':', $this->decryptData($insurePersonRequest->header('X-Encrypted-Data'))));

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
            Log::error($exception->getMessage());

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

            $this->lifeInsuranceService->setCredentials(...explode(':', $this->decryptData($addGeolocationRequest->header('X-Encrypted-Data'))));

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

            $this->lifeInsuranceService->setCredentials(...explode(':', $this->decryptData($finalizeInsuranceRequest->header('X-Encrypted-Data'))));

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

            $this->lifeInsuranceService->setCredentials(...explode(':', $this->decryptData($cancelOrFinalizeInsuranceRequest->header('X-Encrypted-Data'))));
            
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
