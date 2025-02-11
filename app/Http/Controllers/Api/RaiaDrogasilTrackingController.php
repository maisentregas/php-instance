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
        # Definir quais parâmetros eu preciso e tratar dentro do FormRequest
        $validated = $request->validate([
            'tracking_code' => 'required|string',
            'order_id'      => 'required|string',
            'status'        => 'required|string|in:pending,shipped,delivered,cancelled',
            'timestamp'     => 'required|date_format:Y-m-d H:i:s',
        ]);

        try {
            $response = $this->raiaDrogasilService->sendTracking($validated);

            return response()->json([
                'message' => 'Tracking enviado!',
                'data'    => $response
            ], JsonResponse::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'error'   => 'Erro ao enviar tracking',
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
