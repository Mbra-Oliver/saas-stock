<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * @OA\Get(
     *     path="/dashboard/overview",
     *     tags={"Dashboard"},
     *     summary="Vue d'ensemble du tableau de bord",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Statistiques récupérées")
     * )
     */
    public function overview(Request $request)
    {
        try {
            $stats = $this->dashboardService->getOverview($request->user()->company_id);

            return $this->success($stats, 'Statistiques récupérées avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard/sales-chart",
     *     tags={"Dashboard"},
     *     summary="Graphique des ventes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="period", in="query", description="Période (week, month, year)", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Données du graphique")
     * )
     */
    public function salesChart(Request $request)
    {
        try {
            $period = $request->get('period', 'week');
            $data = $this->dashboardService->getSalesChart($request->user()->company_id, $period);

            return $this->success($data, 'Données récupérées avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard/top-products",
     *     tags={"Dashboard"},
     *     summary="Produits les plus vendus",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="limit", in="query", description="Nombre de produits", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Top produits récupérés")
     * )
     */
    public function topProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $products = $this->dashboardService->getTopSellingProducts($request->user()->company_id, $limit);

            return $this->success($products, 'Top produits récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard/recent-movements",
     *     tags={"Dashboard"},
     *     summary="Mouvements de stock récents",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="limit", in="query", description="Nombre de mouvements", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Mouvements récupérés")
     * )
     */
    public function recentMovements(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $movements = $this->dashboardService->getRecentStockMovements($request->user()->company_id, $limit);

            return $this->success($movements, 'Mouvements récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard/recent-orders",
     *     tags={"Dashboard"},
     *     summary="Commandes récentes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="limit", in="query", description="Nombre de commandes", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Commandes récupérées")
     * )
     */
    public function recentOrders(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $orders = $this->dashboardService->getRecentOrders($request->user()->company_id, $limit);

            return $this->success($orders, 'Commandes récupérées');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard/stock-value",
     *     tags={"Dashboard"},
     *     summary="Valeur du stock par entrepôt",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Valeurs récupérées")
     * )
     */
    public function stockValue(Request $request)
    {
        try {
            $stockValue = $this->dashboardService->getStockValueByWarehouse($request->user()->company_id);

            return $this->success($stockValue, 'Valeurs récupérées');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard/inventory-turnover",
     *     tags={"Dashboard"},
     *     summary="Rotation des stocks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="period", in="query", description="Période (month, year)", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Rotation calculée")
     * )
     */
    public function inventoryTurnover(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $turnover = $this->dashboardService->getInventoryTurnover($request->user()->company_id, $period);

            return $this->success($turnover, 'Rotation calculée');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors du calcul', 500);
        }
    }
}
