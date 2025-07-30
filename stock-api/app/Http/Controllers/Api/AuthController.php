<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService){

    }


    public function test():JsonResponse{

        $result = $this->authService->registerUser();

        return $this->success(
            $result['data'],
            'odooflflflfl'
        );

    }

}
