<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Users\RegisterUserRequest;
use App\Service\AuthService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService){

    }

    public function register(RegisterUserRequest $request):JsonResponse{

        dd($request);
        try{

        }catch(Exception $e){


            return $this->error([],$e->getMessage(), 500);
        }

    }




}
