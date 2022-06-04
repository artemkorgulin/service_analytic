<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use App\Repositories\Billing\CompanyTariffRepository;
use App\Services\Billing\OrderService;

class CompanyRepository
{
    /**
     * @param  User  $user
     * @return array
     */
    public function list(User $user): array
    {
        $userCompanies = $user->company()->get();

        $orderService = new OrderService();

        $result = [];

        foreach ($userCompanies as $userCompany) {
            if ($orderService->hasService($userCompany, 'corp')) {
                $userCompanyRelation = UserCompany::where('user_id', $user->id)
                    ->where('company_id', $userCompany->pivot->company_id)
                    ->first();

                $userCompany->roles = $userCompanyRelation->getRoleNames();
                $userCompany->permissions = $userCompanyRelation->getAllPermissions()->map(function($relation) {
                    return $relation->name;
                });

                $userCompany->tariffs = (new CompanyTariffRepository())->getActualTariff($userCompany);

                $result[] = $userCompany;
            }
        }

        return $result;
    }

    public function show(Company $company)
    {
        $userCompany = auth()->user()->userCompany($company->id)->first();
        if ($userCompany && $userCompany->hasPermissionTo('company.users.list')) {
            $company->load(['users' => function ($query) {
                $query->select('users.id', 'name', 'email');
            }]);

            foreach ($company->users as $user) {
                $user->pivot->roles = UserCompany::withTrashed()->where('user_id', $user->pivot->user_id)->where('company_id', $company->id)->first()->roles;

                if ($user->pivot->deleted_at) {
                    $user->pivot->status = __('validation.user_company.statuses.deleted');
                } elseif (!$user->pivot->is_active) {
                    $user->pivot->status = __('validation.user_company.statuses.blocked');
                } else {
                    $user->pivot->status = __('validation.user_company.statuses.active');
                }
            }
        }

        return $company;
    }

    /**
     * Найти компанию по номеру
     */
    public function find($id)
    {
        return Company::find($id);
    }
}
