<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Services\WarehouseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    use ApiResponse;

    public function __construct(
        private WarehouseService $warehouseService
    ) {}

    /**
     * Display a listing of warehouses
     */

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['company_id', 'type', 'active', 'search']);
            $perPage = $request->get('per_page', 10); // Par défaut 15 éléments par page

            $warehouses = $this->warehouseService->getWarehousesPaginated($filters, $perPage);

            Log::channel('warehouse')->info('Warehouses retrieved', [
                'filters' => $filters,
                'total' => $warehouses->total(),
                'per_page' => $perPage,
                'current_page' => $warehouses->currentPage(),
                'user_id' => auth()->id()
            ]);

            return $this->successWithPagination(
                WarehouseResource::collection($warehouses),
                'Warehouses retrieved successfully',
                $warehouses
            );
        } catch (\Exception $e) {
            Log::channel('warehouse')->error('Failed to retrieve warehouses', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            return $this->error('Failed to retrieve warehouses', 500);
        }
    }

    /**
     * Store a newly created warehouse
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        try {
            $warehouse = $this->warehouseService->createWarehouse($request->validated());

            Log::channel('warehouse')->info('Warehouse created', [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->code,
                'company_id' => $warehouse->company_id,
                'user_id' => auth()->id()
            ]);

            return $this->success(
                new WarehouseResource($warehouse),
                'Warehouse created successfully',
                201
            );
        } catch (\Exception $e) {
            Log::channel('warehouse')->error('Failed to create warehouse', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
                'user_id' => auth()->id()
            ]);

            return $this->error('Failed to create warehouse', 500);
        }
    }

    /**
     * Display the specified warehouse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $warehouse = $this->warehouseService->getWarehouse($id);

            Log::channel('warehouse')->info('Warehouse retrieved', [
                'warehouse_id' => $id,
                'user_id' => auth()->id()
            ]);

            return $this->success(
                new WarehouseResource($warehouse),
                'Warehouse retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::channel('warehouse')->error('Failed to retrieve warehouse', [
                'warehouse_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return $this->error('Warehouse not found', 404);
        }
    }

    /**
     * Update the specified warehouse
     */
    public function update(UpdateWarehouseRequest $request, int $id): JsonResponse
    {
        try {
            $warehouse = $this->warehouseService->updateWarehouse($id, $request->validated());

            Log::channel('warehouse')->info('Warehouse updated', [
                'warehouse_id' => $id,
                'changes' => $request->validated(),
                'user_id' => auth()->id()
            ]);

            return $this->success(
                new WarehouseResource($warehouse),
                'Warehouse updated successfully'
            );
        } catch (\Exception $e) {
            Log::channel('warehouse')->error('Failed to update warehouse', [
                'warehouse_id' => $id,
                'error' => $e->getMessage(),
                'data' => $request->validated(),
                'user_id' => auth()->id()
            ]);

            return $this->error('Failed to update warehouse', 500);
        }
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->warehouseService->deleteWarehouse($id);

            Log::channel('warehouse')->info('Warehouse deleted', [
                'warehouse_id' => $id,
                'user_id' => auth()->id()
            ]);

            return $this->success([], 'Warehouse deleted successfully');
        } catch (\Exception $e) {
            Log::channel('warehouse')->error('Failed to delete warehouse', [
                'warehouse_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return $this->error('Failed to delete warehouse', 500);
        }
    }

    // /**
    //  * Get warehouses by company
    //  */
    // public function byCompany(int $companyId): JsonResponse
    // {
    //     try {
    //         $warehouses = $this->warehouseService->getWarehousesByCompany($companyId);

    //         return $this->success(
    //             new WarehouseCollection($warehouses),
    //             'Company warehouses retrieved successfully'
    //         );
    //     } catch (\Exception $e) {
    //         Log::channel('warehouse')->error('Failed to retrieve company warehouses', [
    //             'company_id' => $companyId,
    //             'error' => $e->getMessage(),
    //             'user_id' => auth()->id()
    //         ]);

    //         return $this->error('Failed to retrieve company warehouses', 500);
    //     }
    // }

    // /**
    //  * Toggle warehouse status
    //  */
    // public function toggleStatus(int $id): JsonResponse
    // {
    //     try {
    //         $warehouse = $this->warehouseService->toggleWarehouseStatus($id);

    //         Log::channel('warehouse')->info('Warehouse status toggled', [
    //             'warehouse_id' => $id,
    //             'new_status' => $warehouse->active,
    //             'user_id' => auth()->id()
    //         ]);

    //         return $this->success(
    //             new WarehouseResource($warehouse),
    //             'Warehouse status updated successfully'
    //         );
    //     } catch (\Exception $e) {
    //         Log::channel('warehouse')->error('Failed to toggle warehouse status', [
    //             'warehouse_id' => $id,
    //             'error' => $e->getMessage(),
    //             'user_id' => auth()->id()
    //         ]);

    //         return $this->error('Failed to update warehouse status', 500);
    //     }
    // }
}
