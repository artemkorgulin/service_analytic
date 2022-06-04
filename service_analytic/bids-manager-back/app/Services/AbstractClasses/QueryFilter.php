<?php

namespace App\Services\AbstractClasses;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    public $builder;
    protected $request;

    /**
     * QueryFilter constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $builder
     * @return Builder
     */
    public function apply($builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $filter => $value) {
            if (!$value)
                continue;
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }
        $this->providerFilter();
        return $this->builder;
    }

    /**
     * @return array
     */
    public function filters(): array
    {
        return $this->request->all();
    }

    /**
     * @return array
     */
    public function providerFilter()
    {
        if ($this->request->user()->getAttribute('role') === User::ROLE_PROVIDER) {
            return  $this->builder->where('provider_id', $this->request->user()->getAttribute('provider_id'));
        }elseif ($this->request->user()->getAttribute('role') === User::ROLE_MANAGER) {
            return $this->builder->where('provider_id', $this->request->user()->manager->getattribute('provider_id'));
        }
    }
}
