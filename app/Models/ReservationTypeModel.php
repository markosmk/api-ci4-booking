<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationTypeModel extends Model
{
    protected $table = 'reservation_types';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'description', 'created_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    protected $returnType = 'array';

    /**
     * Find reservation type by name
     * ex: $model->findByName('admin')
     * 
     * @param string $name
     * @return array|null
     */
    public function findByName(string $name)
    {
        return $this->where('name', $name)->first();
    }
}