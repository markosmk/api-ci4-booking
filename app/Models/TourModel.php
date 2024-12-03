<?php

namespace App\Models;

use CodeIgniter\Model;

class TourModel extends Model
{
    protected $table            = 'tours';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'description', 'content', 'media', 'duration', 'capacity', 'price', 'active'];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'        => 'required|min_length[3]',
        'description' => 'required|min_length[10]|max_length[200]',
        'content'     => 'required|min_length[10]|max_length[1000]',
        'duration'    => 'required|integer',
        'capacity'    => 'required|integer',
        'price'       => 'required|decimal',
        'media'       => 'permit_empty|valid_url',
        'active'      => 'permit_empty|in_list[0,1]',
    ];
}