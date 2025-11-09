<?php

namespace App\Service;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories($companyId, $perPage = 15, $filters = [])
    {
        $query = Category::with('parent')
            ->where('company_id', $companyId);

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function createCategory(array $data, $companyId)
    {
        $data['company_id'] = $companyId;
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data)
    {
        $category->update($data);
        return $category->fresh('parent');
    }

    public function deleteCategory(Category $category)
    {
        // Check if category has products
        if ($category->products()->exists()) {
            throw new \Exception('Impossible de supprimer une catégorie ayant des produits');
        }

        // Check if category has children
        if ($category->children()->exists()) {
            throw new \Exception('Impossible de supprimer une catégorie ayant des sous-catégories');
        }

        $category->delete();
    }

    public function getCategoryDetails(Category $category)
    {
        return $category->load(['parent', 'children', 'products']);
    }
}
