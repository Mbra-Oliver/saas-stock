<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Service\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Get(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Liste tous les produits",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Numéro de page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Produits par page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Recherche", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", description="Filtrer par catégorie", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="supplier_id", in="query", description="Filtrer par fournisseur", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", description="Filtrer par statut", @OA\Schema(type="string")),
     *     @OA\Parameter(name="low_stock", in="query", description="Stock bas uniquement", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Liste des produits récupérée"),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['search', 'category_id', 'supplier_id', 'status', 'low_stock']);

            $products = $this->productService->getAllProducts(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $products->items(),
                'Produits récupérés avec succès',
                $products
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération des produits', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Créer un nouveau produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","purchase_price","selling_price"},
     *             @OA\Property(property="name", type="string", example="Produit Test"),
     *             @OA\Property(property="sku", type="string", example="PRD-001"),
     *             @OA\Property(property="barcode", type="string", example="1234567890"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="supplier_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Description du produit"),
     *             @OA\Property(property="purchase_price", type="number", example=5000),
     *             @OA\Property(property="selling_price", type="number", example=7500),
     *             @OA\Property(property="alert_quantity", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Produit créé avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($product, 'Produit créé avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la création du produit', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Détails d'un produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID du produit", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails du produit"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function show(Product $product)
    {
        try {
            // Verify product belongs to user's company
            if ($product->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Produit non trouvé', 404);
            }

            $productDetails = $this->productService->getProductDetails($product);

            return $this->success($productDetails, 'Détails du produit récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération du produit', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Mettre à jour un produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID du produit", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Produit Modifié"),
     *             @OA\Property(property="selling_price", type="number", example=8000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Produit mis à jour"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            // Verify product belongs to user's company
            if ($product->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Produit non trouvé', 404);
            }

            $updatedProduct = $this->productService->updateProduct($product, $request->validated());

            return $this->success($updatedProduct, 'Produit mis à jour avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la mise à jour du produit', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Supprimer un produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID du produit", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Produit supprimé"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function destroy(Product $product)
    {
        try {
            // Verify product belongs to user's company
            if ($product->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Produit non trouvé', 404);
            }

            $this->productService->deleteProduct($product);

            return $this->success([], 'Produit supprimé avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/products/search/{query}",
     *     tags={"Products"},
     *     summary="Rechercher des produits",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="query", in="path", required=true, description="Terme de recherche", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Résultats de la recherche")
     * )
     */
    public function search(Request $request, $query)
    {
        try {
            $products = $this->productService->searchProducts($request->user()->company_id, $query);

            return $this->success($products, 'Recherche effectuée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la recherche', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/products/{id}/image",
     *     tags={"Products"},
     *     summary="Upload image produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID du produit", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Image uploadée")
     * )
     */
    public function uploadImage(Request $request, Product $product)
    {
        try {
            // Verify product belongs to user's company
            if ($product->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Produit non trouvé', 404);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $updatedProduct = $this->productService->uploadImage($product, $request->file('image'));

            return $this->success($updatedProduct, 'Image uploadée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de l\'upload de l\'image', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/products/low-stock",
     *     tags={"Products"},
     *     summary="Produits en stock bas",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste des produits en stock bas")
     * )
     */
    public function lowStock(Request $request)
    {
        try {
            $products = $this->productService->getLowStockProducts($request->user()->company_id);

            return $this->success($products, 'Produits en stock bas récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }
}
