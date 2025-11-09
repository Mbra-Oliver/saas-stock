<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Service\SupplierService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse;

    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * @OA\Get(
     *     path="/suppliers",
     *     tags={"Suppliers"},
     *     summary="Liste tous les fournisseurs",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste des fournisseurs")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['status', 'search']);

            $suppliers = $this->supplierService->getAllSuppliers(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $suppliers->items(),
                'Fournisseurs récupérés avec succès',
                $suppliers
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/suppliers",
     *     tags={"Suppliers"},
     *     summary="Créer un nouveau fournisseur",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Fournisseur ABC"),
     *             @OA\Property(property="company", type="string", example="ABC Company"),
     *             @OA\Property(property="email", type="string", example="contact@abc.com"),
     *             @OA\Property(property="phone", type="string", example="+237690000000"),
     *             @OA\Property(property="address", type="string", example="123 Rue Example"),
     *             @OA\Property(property="city", type="string", example="Douala"),
     *             @OA\Property(property="country", type="string", example="Cameroun"),
     *             @OA\Property(property="tax_number", type="string", example="M123456789"),
     *             @OA\Property(property="payment_terms", type="string", example="Net 30"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Fournisseur créé")
     * )
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->createSupplier(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($supplier, 'Fournisseur créé avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la création', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/suppliers/{id}",
     *     tags={"Suppliers"},
     *     summary="Détails d'un fournisseur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails du fournisseur")
     * )
     */
    public function show(Supplier $supplier)
    {
        try {
            if ($supplier->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Fournisseur non trouvé', 404);
            }

            $supplierDetails = $this->supplierService->getSupplierDetails($supplier);

            return $this->success($supplierDetails, 'Détails récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/suppliers/{id}",
     *     tags={"Suppliers"},
     *     summary="Mettre à jour un fournisseur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Fournisseur mis à jour")
     * )
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        try {
            if ($supplier->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Fournisseur non trouvé', 404);
            }

            $updatedSupplier = $this->supplierService->updateSupplier(
                $supplier,
                $request->validated()
            );

            return $this->success($updatedSupplier, 'Fournisseur mis à jour avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la mise à jour', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/suppliers/{id}",
     *     tags={"Suppliers"},
     *     summary="Supprimer un fournisseur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Fournisseur supprimé")
     * )
     */
    public function destroy(Supplier $supplier)
    {
        try {
            if ($supplier->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Fournisseur non trouvé', 404);
            }

            $this->supplierService->deleteSupplier($supplier);

            return $this->success([], 'Fournisseur supprimé avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }
}
