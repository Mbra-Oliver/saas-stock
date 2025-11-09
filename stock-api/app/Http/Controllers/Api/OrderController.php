<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Order;
use App\Service\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Liste toutes les commandes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Numéro de page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items par page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type", in="query", description="Type de commande", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Statut", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", description="Statut paiement", @OA\Schema(type="string")),
     *     @OA\Parameter(name="warehouse_id", in="query", description="Filtrer par entrepôt", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date_from", in="query", description="Date début", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", description="Date fin", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="search", in="query", description="Recherche", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste des commandes récupérée"),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['type', 'status', 'payment_status', 'warehouse_id', 'date_from', 'date_to', 'search']);

            $orders = $this->orderService->getAllOrders(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $orders->items(),
                'Commandes récupérées avec succès',
                $orders
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération des commandes', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Créer une nouvelle commande",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"warehouse_id","type","items"},
     *             @OA\Property(property="warehouse_id", type="integer", example=1),
     *             @OA\Property(property="type", type="string", example="sale"),
     *             @OA\Property(property="order_number", type="string", example="SO-000001"),
     *             @OA\Property(property="customer_name", type="string", example="Jean Dupont"),
     *             @OA\Property(property="customer_email", type="string", example="jean@example.com"),
     *             @OA\Property(property="customer_phone", type="string", example="+237690000000"),
     *             @OA\Property(property="supplier_id", type="integer", example=1),
     *             @OA\Property(property="tax_rate", type="number", example=19.25),
     *             @OA\Property(property="discount_amount", type="number", example=5000),
     *             @OA\Property(property="shipping_cost", type="number", example=2000),
     *             @OA\Property(property="notes", type="string", example="Commande urgente"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="unit_price", type="number", example=7500),
     *                     @OA\Property(property="tax_rate", type="number", example=19.25),
     *                     @OA\Property(property="discount_amount", type="number", example=500)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Commande créée avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($order, 'Commande créée avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la création de la commande', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Détails d'une commande",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la commande", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails de la commande"),
     *     @OA\Response(response=404, description="Commande non trouvée")
     * )
     */
    public function show(Order $order)
    {
        try {
            // Verify order belongs to user's company
            if ($order->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Commande non trouvée', 404);
            }

            $orderDetails = $this->orderService->getOrderDetails($order);

            return $this->success($orderDetails, 'Détails de la commande récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération de la commande', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Mettre à jour une commande",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la commande", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_name", type="string", example="Jean Dupont Modifié"),
     *             @OA\Property(property="notes", type="string", example="Notes modifiées")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Commande mise à jour"),
     *     @OA\Response(response=404, description="Commande non trouvée")
     * )
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        try {
            // Verify order belongs to user's company
            if ($order->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Commande non trouvée', 404);
            }

            $updatedOrder = $this->orderService->updateOrder($order, $request->validated());

            return $this->success($updatedOrder, 'Commande mise à jour avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Supprimer une commande (soft delete)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la commande", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Commande supprimée"),
     *     @OA\Response(response=404, description="Commande non trouvée")
     * )
     */
    public function destroy(Order $order)
    {
        try {
            // Verify order belongs to user's company
            if ($order->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Commande non trouvée', 404);
            }

            // Only allow deletion of pending orders
            if ($order->status !== 'pending') {
                return $this->error([], 'Impossible de supprimer une commande confirmée', 400);
            }

            $order->delete();

            return $this->success([], 'Commande supprimée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la suppression de la commande', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/orders/{id}/confirm",
     *     tags={"Orders"},
     *     summary="Confirmer une commande",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la commande", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Commande confirmée"),
     *     @OA\Response(response=400, description="Erreur lors de la confirmation")
     * )
     */
    public function confirm(Order $order)
    {
        try {
            // Verify order belongs to user's company
            if ($order->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Commande non trouvée', 404);
            }

            $confirmedOrder = $this->orderService->confirmOrder($order);

            return $this->success($confirmedOrder, 'Commande confirmée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/orders/{id}/cancel",
     *     tags={"Orders"},
     *     summary="Annuler une commande",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la commande", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Commande annulée"),
     *     @OA\Response(response=400, description="Erreur lors de l'annulation")
     * )
     */
    public function cancel(Order $order)
    {
        try {
            // Verify order belongs to user's company
            if ($order->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Commande non trouvée', 404);
            }

            $cancelledOrder = $this->orderService->cancelOrder($order);

            return $this->success($cancelledOrder, 'Commande annulée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/orders/{id}/payment-status",
     *     tags={"Orders"},
     *     summary="Mettre à jour le statut de paiement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la commande", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="paid", enum={"paid", "unpaid", "partial"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Statut de paiement mis à jour"),
     *     @OA\Response(response=400, description="Erreur")
     * )
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        try {
            // Verify order belongs to user's company
            if ($order->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Commande non trouvée', 404);
            }

            $request->validate([
                'status' => 'required|in:paid,unpaid,partial'
            ]);

            $updatedOrder = $this->orderService->updatePaymentStatus($order, $request->status);

            return $this->success($updatedOrder, 'Statut de paiement mis à jour');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la mise à jour', 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/orders/stats",
     *     tags={"Orders"},
     *     summary="Statistiques des commandes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="date_from", in="query", description="Date début", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", description="Date fin", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Statistiques récupérées")
     * )
     */
    public function stats(Request $request)
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);

            $stats = $this->orderService->getOrderStats($request->user()->company_id, $filters);

            return $this->success($stats, 'Statistiques récupérées avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération des statistiques', 500);
        }
    }
}
