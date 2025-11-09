<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\StockInRequest;
use App\Http\Requests\Stock\StockOutRequest;
use App\Http\Requests\Stock\TransferStockRequest;
use App\Http\Requests\Stock\AdjustStockRequest;
use App\Service\StockService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    use ApiResponse;

    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * @OA\Get(
     *     path="/stocks",
     *     tags={"Stock Management"},
     *     summary="Liste tous les stocks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Numéro de page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items par page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="warehouse_id", in="query", description="Filtrer par entrepôt", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="product_id", in="query", description="Filtrer par produit", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="low_stock", in="query", description="Stock bas uniquement", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="search", in="query", description="Recherche", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste des stocks récupérée"),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['warehouse_id', 'product_id', 'low_stock', 'search']);

            $stocks = $this->stockService->getAllStocks(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $stocks->items(),
                'Stocks récupérés avec succès',
                $stocks
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération des stocks', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/stocks/in",
     *     tags={"Stock Management"},
     *     summary="Entrée de stock",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","warehouse_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="warehouse_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=100),
     *             @OA\Property(property="cost_price", type="number", example=5000),
     *             @OA\Property(property="supplier_id", type="integer", example=1),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="reference", type="string", example="PO-001"),
     *             @OA\Property(property="notes", type="string", example="Réception commande fournisseur")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Stock ajouté avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function stockIn(StockInRequest $request)
    {
        try {
            $result = $this->stockService->stockIn(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($result, 'Stock ajouté avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de l\'ajout du stock', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/stocks/out",
     *     tags={"Stock Management"},
     *     summary="Sortie de stock",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","warehouse_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="warehouse_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=10),
     *             @OA\Property(property="cost_price", type="number", example=5000),
     *             @OA\Property(property="customer_id", type="integer", example=1),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="reference", type="string", example="SO-001"),
     *             @OA\Property(property="notes", type="string", example="Vente client")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Stock retiré avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function stockOut(StockOutRequest $request)
    {
        try {
            $result = $this->stockService->stockOut(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($result, 'Stock retiré avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/stocks/transfer",
     *     tags={"Stock Management"},
     *     summary="Transfert de stock entre entrepôts",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","from_warehouse_id","to_warehouse_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="from_warehouse_id", type="integer", example=1),
     *             @OA\Property(property="to_warehouse_id", type="integer", example=2),
     *             @OA\Property(property="quantity", type="integer", example=50),
     *             @OA\Property(property="reference", type="string", example="TRF-001"),
     *             @OA\Property(property="notes", type="string", example="Transfert inter-entrepôts")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Stock transféré avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function transfer(TransferStockRequest $request)
    {
        try {
            $result = $this->stockService->transferStock(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($result, 'Stock transféré avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/stocks/adjust",
     *     tags={"Stock Management"},
     *     summary="Ajustement de stock (inventaire)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","warehouse_id","new_quantity","notes"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="warehouse_id", type="integer", example=1),
     *             @OA\Property(property="new_quantity", type="integer", example=95),
     *             @OA\Property(property="reference", type="string", example="ADJ-001"),
     *             @OA\Property(property="notes", type="string", example="Ajustement suite inventaire physique")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Stock ajusté avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function adjust(AdjustStockRequest $request)
    {
        try {
            $result = $this->stockService->adjustStock(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($result, 'Stock ajusté avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de l\'ajustement du stock', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/stocks/movements",
     *     tags={"Stock Management"},
     *     summary="Historique des mouvements de stock",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Numéro de page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items par page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="product_id", in="query", description="Filtrer par produit", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="warehouse_id", in="query", description="Filtrer par entrepôt", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type", in="query", description="Filtrer par type", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", description="Date début", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", description="Date fin", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Historique récupéré"),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function movements(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['product_id', 'warehouse_id', 'type', 'date_from', 'date_to']);

            $movements = $this->stockService->getStockMovements(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $movements->items(),
                'Mouvements récupérés avec succès',
                $movements
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération des mouvements', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/stocks/low-stock",
     *     tags={"Stock Management"},
     *     summary="Produits en stock bas",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste des produits en stock bas")
     * )
     */
    public function lowStock(Request $request)
    {
        try {
            $products = $this->stockService->getLowStockProducts($request->user()->company_id);

            return $this->success($products, 'Produits en stock bas récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/stocks/product/{product_id}",
     *     tags={"Stock Management"},
     *     summary="Détails du stock d'un produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="product_id", in="path", required=true, description="ID du produit", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails du stock"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function productStock(Request $request, $productId)
    {
        try {
            $stockDetails = $this->stockService->getProductStockDetails(
                $productId,
                $request->user()->company_id
            );

            return $this->success($stockDetails, 'Détails du stock récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }
}
