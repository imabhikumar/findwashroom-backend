<?php

namespace App\Repositories;

use App\Models\Complaint;

class ComplaintRepository
{
    public function create(array $payload): Complaint
    {
        return Complaint::create($payload);
    }
}
