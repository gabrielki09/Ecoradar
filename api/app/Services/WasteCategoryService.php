<?php

namespace App\Services;

use App\Models\Company\Company;
use App\Repositories\Interface\WasteCategoryInterface;

class WasteCategoryService
{
    public function __construct(
        protected WasteCategoryInterface $wasteCategoryRepository
    ) {}

    public function all()
    {
        return $this->wasteCategoryRepository->all(
            parameters:[
                'deletad_at' => null
            ]
        );
    }

    public function store(array $data, Company $company)
    {
        $data['company_id'] = $company->id;
        return $company->customWasteCategories()->create($data);
    }

    public function show(int $id)
    {
        return $this->wasteCategoryRepository->find($id);
    }
}
