<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = ['username', 'password', 'role', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // active protection of data
    protected $returnType = 'array';

    /**
     * Find user by username
     * ex: $model->findByUsername('admin')
     * 
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }
}