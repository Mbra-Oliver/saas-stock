<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Service\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Liste toutes les catégories",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="parent_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Liste des catégories")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['parent_id', 'search']);

            $categories = $this->categoryService->getAllCategories(
                $request->user()->company_id,
                $perPage,
                $filters
            );

            return $this->successWithPagination(
                $categories->items(),
                'Catégories récupérées avec succès',
                $categories
            );
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Créer une nouvelle catégorie",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Électronique"),
     *             @OA\Property(property="slug", type="string", example="electronique"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="parent_id", type="integer"),
     *             @OA\Property(property="image", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Catégorie créée")
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->createCategory(
                $request->validated(),
                $request->user()->company_id
            );

            return $this->success($category, 'Catégorie créée avec succès', 201);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la création', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Détails d'une catégorie",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails de la catégorie")
     * )
     */
    public function show(Category $category)
    {
        try {
            if ($category->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Catégorie non trouvée', 404);
            }

            $categoryDetails = $this->categoryService->getCategoryDetails($category);

            return $this->success($categoryDetails, 'Détails récupérés');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la récupération', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Mettre à jour une catégorie",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Catégorie mise à jour")
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            if ($category->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Catégorie non trouvée', 404);
            }

            $updatedCategory = $this->categoryService->updateCategory(
                $category,
                $request->validated()
            );

            return $this->success($updatedCategory, 'Catégorie mise à jour avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Erreur lors de la mise à jour', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Supprimer une catégorie",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Catégorie supprimée")
     * )
     */
    public function destroy(Category $category)
    {
        try {
            if ($category->company_id !== auth()->user()->company_id) {
                return $this->error([], 'Catégorie non trouvée', 404);
            }

            $this->categoryService->deleteCategory($category);

            return $this->success([], 'Catégorie supprimée avec succès');
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], $e->getMessage(), 400);
        }
    }
}
