<?php

namespace App\Services;

use App\Models\Company;

class CompanyService
{

    public function create(array $data): Company
    {
        $company = Company::create($data);
        return $company;
    }
}
