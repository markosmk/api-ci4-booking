<?php

namespace App\Controllers;

use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Controllers\ResourceBaseController;

class AuthController extends ResourceBaseController
{
    public function login()
    {
        $model = new UserModel();
        $data = $this->request->getJSON(true);

        if (!$model->validateData($data, 'login')) {
            return $this->failValidationErrors($this->model->getErrors());
        }

        // search user, if exists
        $user = $model->where('username', $data['username'])->first();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->failValidationErrors(['message' => 'Nombre de usuario o contraseña incorrectos']);
        }

        $token = $this->generateJWT($user);
        return $this->respond(['message' => 'Login exitoso', 'token' => $token]);
    }

    public function me()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $this->response->setJSON((array) $decoded);
        } catch (\Exception $e) {
            return $this->response->setJSON(['message' => 'Token inválido'])->setStatusCode(401);
        }
    }

    protected function generateJWT($user)
    {
        $key = getenv('JWT_SECRET');
        $payload = [
            'id' => $user['id'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
        ];
        return JWT::encode($payload, $key, 'HS256');
    }
}
