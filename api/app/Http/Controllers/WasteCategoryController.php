<?php

namespace App\Http\Controllers;

use App\Enum\CompanyUsers\RoleUser;
use App\Http\Requests\WasteCategory\WasteCategoryStoreRequest;
use App\Http\Resources\WastCategoryResource;
use App\Models\Company\Company;
use App\Services\WasteCategoryService;
use Illuminate\Http\Request;

class WasteCategoryController extends Controller
{
    public function __construct(
        protected WasteCategoryService $wasteCategoryService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return apiSuccess(
            'Todas as categorias de resíduos',
            [
                'waste_categories' => $this->wasteCategoryService->all()
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WasteCategoryStoreRequest $req, Company $company)
    {
        $allowed = $req->user()->companies()
            ->whereKey($company->id)
            ->wherePivot('role', RoleUser::RESPONSIBLE->value)
            ->exists();

        abort_unless($allowed, 403);

        return apiSuccess(
            'Categoria cadastrada com sucesso!',
            [
                'waste_category' => new WastCategoryResource($this->wasteCategoryService->store($req->validated(), $company))
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return apiSuccess(
            'Dados da categoria!',
            [
                'waste_category' => new WastCategoryResource($this->wasteCategoryService->show($id))
            ],
            201
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
