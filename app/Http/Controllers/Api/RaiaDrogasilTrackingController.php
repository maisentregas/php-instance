<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendTrackingRequest;
use App\Services\RaiaDrogasilService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RaiaDrogasilTrackingController extends Controller
{
    protected $raiaDrogasilService;

    public function __construct(RaiaDrogasilService $raiaDrogasilService)
    {
        $this->raiaDrogasilService = $raiaDrogasilService;
    }

    public function sendTracking(SendTrackingRequest $request)
    {
        try {
            $response = $this->raiaDrogasilService->sendTracking($request->validated());

            return response()->json([
                'message' => 'Tracking sent!',
                'data' => $response
            ], JsonResponse::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'error' => 'Error while triyng to send tracking.',
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
