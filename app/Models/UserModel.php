<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'username', 'password', 'role', 'email', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // active protection of data
    protected $returnType = 'array';


    protected $validationRules = [
        'name'     => 'permit_empty|max_length[100]|alpha_numeric_space',
        'username' => 'required|min_length[4]|max_length[50]|alpha_numeric|is_unique[users.username]',
        'password' => 'required|min_length[8]|max_length[255]',
        'email'    => 'required|valid_email|max_length[150]|is_unique[users.email]',
        'role'     => 'permit_empty|in_list[admin,superadmin,user]',
    ];

    protected $validationMessages = [
        'username' => [
            'required'    => 'El nombre de usuario es obligatorio.',
            'min_length'  => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'max_length'  => 'El nombre de usuario no puede tener más de 50 caracteres.',
            'alpha_numeric' => 'El nombre de usuario solo puede contener caracteres alfanuméricos.',
            'is_unique'   => 'El nombre de usuario ya está en uso.',
        ],
        'password' => [
            'required'    => 'La contraseña es obligatoria.',
            'min_length'  => 'La contraseña debe tener al menos 8 caracteres.',
            'max_length'  => 'La contraseña no puede tener más de 255 caracteres.',
        ],
        'email' => [
            'required'    => 'El correo electronico es obligatorio.',
            'valid_email' => 'El correo electronico no es valido.',
            'max_length'  => 'El correo electronico no puede tener más de 150 caracteres.',
            'is_unique'   => 'El correo electronico ya está en uso.',
        ],
        'role' => [
            'in_list' => 'El rol debe ser "admin", "superadmin" o "user".',
        ],
    ];

    protected $errors = [];

    public function validateData(array $data, string $context = 'create'): bool
    {
        $validation = \Config\Services::validation();

        // follow context
        if ($context === 'update') {
            $rules = [
                'id'    => 'max_length[19]|is_natural_no_zero',
                'name'  => 'permit_empty|max_length[100]|alpha_numeric_space',
                'username' => 'permit_empty|min_length[4]|max_length[50]|alpha_numeric|is_unique[users.username,id,{id}]',
                'password' => 'permit_empty|min_length[8]|max_length[255]',
                'email'    => 'permit_empty|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
                'role'     => 'permit_empty|in_list[admin,superadmin,user]',
            ];
        } elseif ($context === 'updateSelf') {
            $rules = [
                'id'    => 'max_length[19]|is_natural_no_zero',
                'name'  => 'permit_empty|max_length[100]|alpha_numeric_space',
                'username' => 'permit_empty|min_length[4]|max_length[50]|alpha_numeric|is_unique[users.username,id,{id}]',
                'password' => 'required|min_length[8]|max_length[255]',
            ];
        } else {
            $rules = $this->validationRules;
        }

        $validation->setRules($rules, $this->validationMessages);

        if (!$validation->run($data)) {
            $this->errors = $validation->getErrors();
            return false;
        }

        return true;
    }

    public function validateLogin(array $data): bool
    {
        $validation = \Config\Services::validation();

        $rules = [
            'username' => 'permit_empty|min_length[3]|max_length[50]|alpha_numeric',
            'email'    => 'permit_empty|valid_email|max_length[150]',
            'password' => 'required|min_length[8]|max_length[255]',
        ];

        $validation->setRules($rules, $this->validationMessages);

        if (!$validation->run($data)) {
            $this->errors = $validation->getErrors();
            return false;
        }

        // validate username or email, if both are empty
        if (empty($data['username']) && empty($data['email'])) {
            $this->errors['username_email'] = 'Debes proporcionar tu nombre de usuario o email.';
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

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