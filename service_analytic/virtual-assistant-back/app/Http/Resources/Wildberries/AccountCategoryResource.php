<?php

namespace App\Http\Resources\Wildberries;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->category->id,
            'name' => $this->object,
            'parent_id' => $this->directory->id ?? null,
            'parent_name' => $this->parent,
        ];
    }
}
