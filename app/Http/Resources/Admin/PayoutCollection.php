<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PayoutCollection extends ResourceCollection
{
    public $collects = PayoutResource::class;
}