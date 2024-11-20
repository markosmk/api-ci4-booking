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
        $users = $this->model->findAll();

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
        $authUser = $this->request->user;
        $data = $this->request->getJSON(true);

        $userToUpdate = $this->model->find($authUser['id']);
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

        if (!$this->model->validateData($data + ['id' => $authUser['id']], 'updateSelf')) {
            return $this->failValidationErrors($this->model->getErrors());
        }

        if (isset($updateData['password'])) {
            $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
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
            return $this->respondWithServerError('No se pudo actualizar tu informacion');
        }

    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Usuario eliminado con éxito']);
        }

        return $this->failServerError('No se pudo eliminar el usuario');
    }
}
