<?php

namespace App\Models;

use CodeIgniter\Model;

class TourModel extends Model
{
    protected $table            = 'tours';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'description', 'duration', 'capacity', 'active'];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'        => 'required|min_length[3]',
        'description' => 'required',
        'duration'    => 'required|integer',
        'capacity'    => 'required|integer',
    ];
}