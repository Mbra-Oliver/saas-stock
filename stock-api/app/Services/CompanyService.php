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

    public function getDefaultEnterpriseOfUser()
    {
        return Company::where('user_id', auth()->user()->id)->first();
    }
}
