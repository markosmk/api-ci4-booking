<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class UserController extends ResourceBaseController
{
    protected $modelName = UserModel::class;
    protected $format    = 'json';

    public function index()
    {
        $users = $this->model->orderBy('users.created_at', 'desc')->findAll();

        // not show password in list users
        foreach ($users as $key => $user) {
            unset($users[$key]['password']);

        }
        return $this->respond($users);
    }

    public function create()
    {
        // get data from request
        $data = $this->request->getJSON(true);

        // validation
        if (!$this->model->validateData($data, 'create')) {
            return $this->failValidationErrors($this->model->getErrors());
        }

        $userData = [
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'     => $data['role'],
        ];

        // save in database
        $userModel = new UserModel();
        if ($userModel->insert($userData)) {
            $userData['id'] = $userModel->getInsertID();
            unset($userData['password']);
            return $this->respondCreated([
                'message' => 'Usuario creado con éxito',
                'data'    => $userData,
            ]);
        }

        return $this->failServerError('No se pudo crear el usuario');
    }

    /** this method must be used for superadmin */
    public function update($id = null)
    {
        $authUser = $this->request->user;
        $data = $this->request->getJSON(true);

        // 1) Verify if user exists
        $userToUpdate = $this->model->find($id);

        if (!$userToUpdate) {
            return $this->failNotFound('Usuario no encontrado');
        }

        // 2) build obj to validating
        $updateData = [];

        if (isset($data['username']) && $data['username'] !== $userToUpdate['username']) {
            $updateData['username'] = $data['username'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        if (isset($data['role']) && $data['role'] !== $userToUpdate['role']) {
            // not allow to change role (for superadmin)
            if ($authUser['id'] == $id && $authUser['role'] === 'superadmin' && $data['role'] !== 'superadmin') {
                return $this->failForbidden('No puedes cambiarte tu propio rol');
            }
            $updateData['role'] = $data['role'];
        }

        // If nothing to update
        if (empty($updateData)) {
            return $this->failValidationErrors(['message' => 'No hay cambios para actualizar.']);
        }

        // 3) validate data to update
        if (!$this->model->validateData($updateData + ['id' => $id], 'update')) {
            return $this->failValidationErrors($this->model->getErrors());
        }


        if (isset($updateData['password'])) {
            $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
        }

        // 4) process update
        try {
            if (!$this->model->update($id, $updateData)) {
                return $this->failValidationErrors($this->model->errors());
            }
            // only show that data is changed
            foreach ($updateData as $key => $value) {
                $updateData[$key] = 'actualizado';
            }
            return $this->respondUpdated([
                'message' => 'Usuario actualizado con éxito',
                'data'    => $updateData,
            ]);
        } catch (DatabaseException $e) {
            log_message('error', 'Error de base de datos: ' . $e->getMessage());
            return $this->respondWithServerError('Ocurrió un problema al procesar su solicitud. Por favor, inténtelo más tarde.');

        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->respondWithServerError('No se pudo actualizar el usuario');
        }
    }

    /** this method must be used for updating the current user */
    public function updateSelf()
    {
        sleep(1);
        $authUser = $this->request->user;
        $data = $this->request->getJSON(true);

        $userToUpdate = $this->model->find($authUser['id']);
        if (!$userToUpdate) {
            return $this->failNotFound('Usuario no encontrado');
        }

        if (!$this->model->validateData($data + ['id' => $authUser['id']], 'updateSelf')) {
            return $this->failValidationErrors($this->model->getErrors());
        }

        // validating password if is same into userToUpdate
        if (isset($data['password']) && !password_verify($data['password'], $userToUpdate['password'])) {
            return $this->failValidationErrors(['password' => 'La contraseña es incorrecta']);
        }

        // 2) build obj to validating
        $updateData = [
            'name' => isset($data['name']) ? $data['name'] : $userToUpdate['name']
        ];

        // if (isset($data['name']) && $data['name'] !== $userToUpdate['name']) {
        //     $updateData['name'] = $data['name'];
        // }

        if (isset($data['email']) && $data['email'] !== $userToUpdate['email']) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['username']) && $data['username'] !== $userToUpdate['username']) {
            $updateData['username'] = $data['username'];
        }

        if (isset($data['newPassword']) && !empty($data['newPassword'])) {
            // validate newPassword with this rule password
            $validation = \Config\Services::validation();
            $rules = [
                'newPassword' => 'required|min_length[8]|max_length[255]',
            ];
            $validation->setRules($rules);
            if (!$validation->run($data)) {
                return $this->failValidationErrors($validation->getErrors());
            }

            // verify that new password is not same current password
            if (password_verify($data['newPassword'], $userToUpdate['password'])) {
                return $this->failValidationErrors(['newPassword' => 'La nueva contraseña no puede ser la misma que la actual.']);
            }
            $updateData['password'] = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        }

        try {
            if (!$this->model->update($authUser['id'], $updateData)) {
                return $this->failValidationErrors($this->model->errors());
            }
            // only show that data is changed
            foreach ($updateData as $key => $value) {
                $updateData[$key] = 'actualizado';
            }
            return $this->respondUpdated([
                'message' => 'Los Datos han sido actualizado con éxito',
                'data'    => $updateData,
            ]);
        } catch (DatabaseException $e) {
            log_message('error', 'Error de base de datos: ' . $e->getMessage());
            return $this->respondWithServerError('Ocurrió un problema al procesar su solicitud. Por favor, inténtelo más tarde.');

        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->respondWithServerError('No se pudo actualizar tu informacion, intenta mas tarde o contacta a un administrador.');
        }

    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Usuario eliminado con éxito']);
        }

        return $this->failServerError('No se pudo eliminar el usuario');
    }

    public function me()
    {
        $authUser = $this->request->user;

        if (!$authUser) {
            return $this->failUnauthorized('No tienes autorización para acceder a este recurso');
        }

        $user = $this->model->find($authUser['id']);
        if (!$user) {
            return $this->failNotFound('Usuario no encontrado');
        }

        return $this->respond([
            'message' => 'Datos del usuario',
            'user'    => [
                'id'       => $user['id'],
                'name'     => $user['name'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ],
        ]);
    }
}