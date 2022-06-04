<?php

namespace App\Http\Resources\Wildberries;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AccountCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->collection,
            "next_page_url" => $this->nextPageUrl(),
            "path" => $this->path(),
            "per_page" => $this->perPage(),
            "prev_page_url" => $this->previousPageUrl(),
            "to" => $this->lastItem(),
            "total" => $this->total(),
        ];
    }
}
