<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Retourne une réponse JSON standardisée pour une opération réussie.
     *
     * @param mixed $data Les données à retourner dans la réponse.
     * @param string $message Message de succès.
     * @param int $statusCode Code HTTP (par défaut 200).
     * @return JsonResponse
     */
    protected function success(
        $data = [],
        string $message = 'Opération éffectuée avec succès',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Retourne une réponse JSON standardisée pour une erreur.
     *
     * @param mixed $errors Détails ou messages d'erreur.
     * @param string $message Message d'erreur global.
     * @param int $statusCode Code HTTP (par défaut 400).
     * @return JsonResponse
     */
    protected function error(
        $errors = [],
        string $message = 'Une erreur est survenue',
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Retourne une réponse JSON standardisée pour une liste paginée.
     *
     * @param mixed $data Données à retourner.
     * @param string $message Message de succès.
     * @param LengthAwarePaginator $paginator Objet de pagination Laravel.
     * @param int $statusCode Code HTTP (par défaut 200).
     * @return JsonResponse
     */
    protected function successWithPagination(
        $data = [],
        string $message = 'Donnée récupérée avec succès',
        LengthAwarePaginator $paginator,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_page' => $paginator->hasMorePages(),
                'next_page_url' => $paginator->nextPageUrl(),
                'previous_page_url' => $paginator->previousPageUrl(),
            ]
        ], $statusCode);
    }

    /**
     * Retourne une réponse JSON standardisée pour une erreur de validation.
     *
     * @param mixed $errors Détails des erreurs de validation.
     * @param string $message Message général d'erreur (par défaut "Erreur de validation").
     * @return JsonResponse
     */
    protected function validationError(
        $errors = [],
        string $message = 'Erreur de validation '
    ): JsonResponse {


        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }
}
