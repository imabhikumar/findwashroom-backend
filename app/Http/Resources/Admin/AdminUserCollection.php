<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AdminUserCollection extends ResourceCollection
{
    public $collects = AdminUserResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->map(fn ($user) => (new AdminUserResource($user))->toArray($request))->all();
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'total' => $this->resource->total(),
                'page' => $this->resource->currentPage(),
                'per_page' => $this->resource->perPage(),
                'last_page' => $this->resource->lastPage(),
            ],
        ];
    }
}