<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Service\WarehouseService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    use ApiResponse;

    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * @OA\Get(
     *     path="/warehouses",
     *     tags={"Warehouses"},
     *     summary="Liste tous les entrepôts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste des entrepôts")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['status', 'search']);

            $warehouses = $this->warehouseService->getAllWarehouses(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $warehouses->items(),
                'Entrepôts récupérés avec succès',
                $warehouses
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/warehouses",
     *     tags={"Warehouses"},
     *     summary="Créer un nouvel entrepôt",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","code"},
     *             @OA\Property(property="name", type="string", example="Entrepôt Central"),
     *             @OA\Property(property="code", type="string", example="WH-001"),
     *             @OA\Property(property="manager_id", type="integer", example=1),
     *             @OA\Property(property="address", type="string", example="123 Rue Example"),
     *             @OA\Property(property="city", type="string", example="Douala"),
     *             @OA\Property(property="country", type="string", example="Cameroun"),
     *             @OA\Property(property="phone", type="string", example="+237690000000"),
     *             @OA\Property(property="email", type="string", example="warehouse@example.com"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Entrepôt créé")
     * )
     */
    public function store(StoreWarehouseRequest $request)
    {
        try {
            $warehouse = $this->warehouseService->createWarehouse(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($warehouse, 'Entrepôt créé avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la création', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/warehouses/{id}",
     *     tags={"Warehouses"},
     *     summary="Détails d'un entrepôt",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails de l'entrepôt")
     * )
     */
    public function show(Warehouse $warehouse)
    {
        try {
            if ($warehouse->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Entrepôt non trouvé', 404);
            }

            $warehouseDetails = $this->warehouseService->getWarehouseDetails($warehouse);

            return $this->success($warehouseDetails, 'Détails récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/warehouses/{id}",
     *     tags={"Warehouses"},
     *     summary="Mettre à jour un entrepôt",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Entrepôt Modifié")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Entrepôt mis à jour")
     * )
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        try {
            if ($warehouse->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Entrepôt non trouvé', 404);
            }

            $updatedWarehouse = $this->warehouseService->updateWarehouse(
                $warehouse,
                $request->validated()
            );

            return $this->success($updatedWarehouse, 'Entrepôt mis à jour avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la mise à jour', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/warehouses/{id}",
     *     tags={"Warehouses"},
     *     summary="Supprimer un entrepôt",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Entrepôt supprimé")
     * )
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            if ($warehouse->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Entrepôt non trouvé', 404);
            }

            $this->warehouseService->deleteWarehouse($warehouse);

            return $this->success([], 'Entrepôt supprimé avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }
}
