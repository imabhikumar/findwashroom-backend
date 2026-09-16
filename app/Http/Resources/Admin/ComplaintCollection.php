<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ComplaintCollection extends ResourceCollection
{
    public $collects = ComplaintResource::class;
}