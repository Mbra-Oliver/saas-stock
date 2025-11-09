<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\HuggingFaceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AIController extends Controller
{
    use ApiResponse;

    protected $aiService;

    public function __construct(HuggingFaceService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * @OA\Post(
     *     path="/ai/forecast",
     *     tags={"AI & Analytics"},
     *     summary="Prévision de stock avec IA",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="days", type="integer", example=30, description="Nombre de jours à prévoir")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Prévisions générées"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function forecast(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'days' => 'nullable|integer|min:1|max:90'
            ]);

            $forecast = $this->aiService->forecastStock(
                $request->user()->company_id,
                $request->product_id,
                $request->get('days', 30)
            );

            return $this->success($forecast, 'Prévisions générées avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la génération des prévisions', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/ai/assistant",
     *     tags={"AI & Analytics"},
     *     summary="Assistant IA pour questions sur le stock",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question"},
     *             @OA\Property(property="question", type="string", example="Quels sont mes produits en stock bas?")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Réponse de l'assistant"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function assistant(Request $request)
    {
        try {
            $request->validate([
                'question' => 'required|string|max:500'
            ]);

            $response = $this->aiService->askAssistant(
                $request->user()->company_id,
                $request->question
            );

            return $this->success($response, 'Réponse générée');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la génération de la réponse', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/ai/ocr",
     *     tags={"AI & Analytics"},
     *     summary="OCR pour factures/bons de livraison",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary", description="Image de la facture/bon")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Texte extrait et données parsées"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function ocr(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,pdf|max:5120'
            ]);

            // Convert image to base64
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->path()));

            $result = $this->aiService->processOCR($imageData);

            return $this->success($result, 'Image traitée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors du traitement de l\'image', 500);
        }
    }
}
