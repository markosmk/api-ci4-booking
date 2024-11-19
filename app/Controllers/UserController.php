<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    protected $modelName = UserModel::class; 
    protected $format    = 'json'; // for type response

    public function create()
    {
        // get data from request
        $data = $this->request->getJSON(true);

        // basic validation
        if (!isset($data['username']) || !isset($data['password']) || !isset($data['role'])) {
            return $this->failValidationErrors('Faltan datos obligatorios: username, password, role');
        }

        // only rol admin or superadmin
        $validRoles = ['admin', 'superadmin'];
        if (!in_array($data['role'], $validRoles)) {
            return $this->failValidationErrors('El rol debe ser "admin" o "superadmin"');
        }

        // user exists?
        $userModel = new UserModel();
        if ($userModel->where('username', $data['username'])->first()) {
            return $this->failResourceExists('El username ya está en uso');
        }

        $userData = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT), // Encriptar la contraseña
            'role'     => $data['role'],
        ];

        // save in database
        if ($userModel->insert($userData)) {
            return $this->respondCreated([
                'message' => 'Usuario creado con éxito',
                'data'    => $userData,
            ]);
        }

        return $this->failServerError('No se pudo crear el usuario');
    }
}