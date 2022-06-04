<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CompanyDeleteRequest;
use App\Http\Requests\Api\CompanyShowRequest;
use App\Http\Requests\Api\CompanyStoreRequest;
use App\Http\Requests\Api\CompanyUpdateRequest;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Services\CompanyService;
use App\Services\UserService;

/**
 * Ресурсный контроллер для управления компаниями клиента
 */
class CompanyController extends Controller
{
    /**
     * Вывод всех компаний привязанных к клиенту
     *
     * @param  CompanyRepository  $companyRepository
     * @return mixed
     */
    public function index(UserService $userService)
    {
        return response()->api_success($userService->setCompaniesCache());
    }

    /**
     * @param CompanyStoreRequest $request
     * @param CompanyService $companyService
     * @return mixed
     */
    public function store(CompanyStoreRequest $request, CompanyService $companyService)
    {
        $company = $companyService->findOrCreateCompany($request->validated());
        $companyService->adoptCompany($company, auth()->user());

        return response()->api_success($company);
    }

    /**
     * @param Company $company
     * @param CompanyShowRequest $companyShowRequest
     * @param CompanyRepository $companyRepository
     * @return mixed
     */
    public function show(Company $company, CompanyShowRequest $companyShowRequest, CompanyRepository $companyRepository)
    {
        return response()->api_success($companyRepository->show($company));
    }

    /**
     * @param CompanyUpdateRequest $request
     * @param Company $company
     * @param CompanyService $companyService
     * @return mixed
     */
    public function update(CompanyUpdateRequest $request, Company $company, CompanyService $companyService)
    {
        $companyService->updateCompany($company, $request->validated());

        return response()->api_success($company);
    }

    /**
     * @param Company $company
     * @param CompanyDeleteRequest $companyDeleteRequest
     * @param CompanyService $companyService
     * @return mixed
     */
    public function destroy(Company $company, CompanyDeleteRequest $companyDeleteRequest, CompanyService $companyService)
    {
        $companyService->deleteCompany($company);

        return response()->api_success($company);
    }
}
