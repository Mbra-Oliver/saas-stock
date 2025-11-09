<?php

namespace App\Service;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    private $apiKey;
    private $apiUrl = 'https://api-inference.huggingface.co/models/';

    public function __construct()
    {
        $this->apiKey = env('HUGGINGFACE_API_KEY');
    }

    /**
     * Stock forecasting using time series model
     */
    public function forecastStock($companyId, $productId, $days = 30)
    {
        try {
            // Get historical stock movement data
            $movements = StockMovement::whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('product_id', $productId)
            ->where('created_at', '>=', now()->subDays(90))
            ->orderBy('created_at', 'asc')
            ->get();

            // Prepare time series data
            $timeSeries = [];
            foreach ($movements as $movement) {
                $timeSeries[] = [
                    'date' => $movement->created_at->format('Y-m-d'),
                    'quantity' => $movement->type === 'in' ? $movement->quantity : -$movement->quantity
                ];
            }

            // If no Hugging Face API key, return simple moving average forecast
            if (!$this->apiKey) {
                return $this->simpleMovingAverageForecast($timeSeries, $days);
            }

            // Call Hugging Face API for advanced forecasting
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . 'facebook/prophet', [
                'inputs' => json_encode($timeSeries),
                'parameters' => [
                    'forecast_days' => $days
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Fallback to simple forecast if API fails
            return $this->simpleMovingAverageForecast($timeSeries, $days);

        } catch (\Exception $e) {
            Log::error('Stock forecast error: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la prévision du stock');
        }
    }

    /**
     * AI Assistant chatbot for stock queries
     */
    public function askAssistant($companyId, $question)
    {
        try {
            // Get context about company's stock
            $context = $this->getCompanyStockContext($companyId);

            // Prepare prompt for the AI
            $prompt = "Tu es un assistant de gestion de stock. Contexte de l'entreprise:\n";
            $prompt .= "- Total produits: {$context['total_products']}\n";
            $prompt .= "- Produits en stock bas: {$context['low_stock_count']}\n";
            $prompt .= "- Valeur totale du stock: {$context['total_stock_value']} FCFA\n\n";
            $prompt .= "Question: {$question}\n\nRéponds de manière concise et précise.";

            if (!$this->apiKey) {
                return [
                    'response' => 'Service IA non configuré. Veuillez configurer la clé API Hugging Face.',
                    'context' => $context
                ];
            }

            // Call Hugging Face text generation API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . 'mistralai/Mistral-7B-Instruct-v0.1', [
                'inputs' => $prompt,
                'parameters' => [
                    'max_new_tokens' => 500,
                    'temperature' => 0.7,
                    'top_p' => 0.95,
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'response' => $result[0]['generated_text'] ?? 'Aucune réponse générée',
                    'context' => $context
                ];
            }

            return [
                'response' => 'Erreur lors de la génération de la réponse',
                'context' => $context
            ];

        } catch (\Exception $e) {
            Log::error('AI Assistant error: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la consultation de l\'assistant IA');
        }
    }

    /**
     * OCR for invoice/delivery note processing
     */
    public function processOCR($imageBase64)
    {
        try {
            if (!$this->apiKey) {
                return [
                    'error' => 'Service OCR non configuré. Veuillez configurer la clé API Hugging Face.'
                ];
            }

            // Call Hugging Face OCR API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . 'microsoft/trocr-base-printed', [
                'inputs' => $imageBase64
            ]);

            if ($response->successful()) {
                $ocrText = $response->json();

                // Parse the OCR text to extract invoice data
                $parsedData = $this->parseInvoiceText($ocrText);

                return [
                    'raw_text' => $ocrText,
                    'parsed_data' => $parsedData
                ];
            }

            return [
                'error' => 'Erreur lors du traitement OCR'
            ];

        } catch (\Exception $e) {
            Log::error('OCR processing error: ' . $e->getMessage());
            throw new \Exception('Erreur lors du traitement OCR');
        }
    }

    /**
     * Simple moving average forecast (fallback)
     */
    private function simpleMovingAverageForecast($timeSeries, $days)
    {
        if (empty($timeSeries)) {
            return [
                'forecast' => [],
                'method' => 'simple_moving_average',
                'message' => 'Pas assez de données historiques'
            ];
        }

        // Calculate 7-day moving average
        $quantities = array_column($timeSeries, 'quantity');
        $avg = array_sum(array_slice($quantities, -7)) / min(7, count($quantities));

        $forecast = [];
        $lastDate = end($timeSeries)['date'];

        for ($i = 1; $i <= $days; $i++) {
            $forecastDate = date('Y-m-d', strtotime($lastDate . " +{$i} days"));
            $forecast[] = [
                'date' => $forecastDate,
                'predicted_quantity' => round($avg, 2)
            ];
        }

        return [
            'forecast' => $forecast,
            'method' => 'simple_moving_average',
            'average_daily_movement' => round($avg, 2)
        ];
    }

    /**
     * Get company stock context
     */
    private function getCompanyStockContext($companyId)
    {
        $totalProducts = Product::where('company_id', $companyId)->count();

        $lowStockCount = Stock::whereHas('product', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereColumn('quantity', '<=', 'alert_quantity')->count();

        $totalStockValue = Stock::whereHas('product', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
        ->join('products', 'stocks.product_id', '=', 'products.id')
        ->sum(\DB::raw('stocks.quantity * products.purchase_price'));

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'total_stock_value' => $totalStockValue
        ];
    }

    /**
     * Parse invoice text from OCR
     */
    private function parseInvoiceText($ocrText)
    {
        // Basic parsing logic - can be enhanced
        $parsed = [
            'invoice_number' => null,
            'date' => null,
            'supplier' => null,
            'total_amount' => null,
            'items' => []
        ];

        // Extract invoice number (basic regex)
        if (preg_match('/invoice[:\s#]*([A-Z0-9-]+)/i', $ocrText, $matches)) {
            $parsed['invoice_number'] = $matches[1];
        }

        // Extract date (basic regex)
        if (preg_match('/\d{2}[-\/]\d{2}[-\/]\d{4}/', $ocrText, $matches)) {
            $parsed['date'] = $matches[0];
        }

        // Extract total amount (basic regex)
        if (preg_match('/total[:\s]*(\d+[\.,]?\d*)/i', $ocrText, $matches)) {
            $parsed['total_amount'] = floatval(str_replace(',', '.', $matches[1]));
        }

        return $parsed;
    }
}
