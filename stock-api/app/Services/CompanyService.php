<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyService
{

    public function create(array $data): Company
    {
        $company = Company::create($data);
        return $company;
    }

    public function getDefaultEnterpriseOfUser(int $userId): ?Company
    {
        if (!$userId) {
            return null;
        }
        return Company::where('user_id', $userId)->where('active', true)->first();
    }
}
