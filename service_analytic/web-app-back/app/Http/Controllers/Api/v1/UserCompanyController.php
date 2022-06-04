<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserCompanyAddRequest;
use App\Http\Requests\Api\UserCompanyDeleteRequest;
use App\Http\Requests\Api\UserCompanyRoleAddRequest;
use App\Http\Requests\Api\UserCompanyRoleDeleteRequest;
use App\Http\Requests\Api\UserCompanyRoleRequest;
use App\Http\Requests\Api\UserCompanySearchRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\CompanyService;

class UserCompanyController extends Controller
{
    /**
     * @param UserCompanyAddRequest $request
     * @param CompanyService $companyService
     * @return mixed
     */
    public function store(UserCompanyAddRequest $request, CompanyService $companyService)
    {
        return response()->api_success($companyService->userCompanyAdd($request->user_id, $request->company_id));
    }

    /**
     * @param UserCompanyDeleteRequest $request
     * @param CompanyService $companyService
     * @return mixed
     */
    public function destroy(UserCompanyDeleteRequest $request, CompanyService $companyService)
    {
        return response()->api_success($companyService->userCompanyDelete($request->user_id, $request->company_id));
    }

    /**
     * @param  UserCompanySearchRequest  $request
     * @param  CompanyService  $companyService
     * @return mixed
     */
    public function search(UserCompanySearchRequest $request, CompanyService $companyService)
    {
        return response()->api_success($companyService->searchUser($request->email));
    }

    public function userRoles(Company $company, User $user, UserCompanyRoleRequest $request)
    {
        $roles = $user->userCompany($company->id)->first()->roles;

        return response()->api_success($roles);
    }

    /**
     * @param Company $company
     * @param User $user
     * @param UserCompanyRoleAddRequest $request
     * @param CompanyService $companyService
     * @return mixed
     */
    public function userSetRole(Company $company, User $user, UserCompanyRoleAddRequest $request, CompanyService $companyService)
    {
        return response()->api_success($companyService->userSetRole($company, $user, $request->roles));
    }

    /**
     * @param  Company  $company
     * @param  User  $user
     * @param  UserCompanyRoleDeleteRequest  $request
     * @param  CompanyService  $companyService
     * @return mixed
     */
    public function userDeleteRole(Company $company, User $user, UserCompanyRoleDeleteRequest $request, CompanyService $companyService)
    {
        return response()->api_success($companyService->userDeleteRole($company, $user, $request->roles));
    }

    /**
     * @return mixed
     */
    public function getAllRoles()
    {
        return response()->api_success(Role::where('guard_name', 'company')->where('name', '!=', 'company.owner')->get());
    }
}
