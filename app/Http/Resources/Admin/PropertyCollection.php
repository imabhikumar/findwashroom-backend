<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyCollection extends ResourceCollection
{
    public $collects = PropertyResource::class;
}