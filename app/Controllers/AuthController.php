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
        //TODO: Auditory Logs, register intent of fail login, to detect attacks
        //TODO: Rate Limiting, prevent brute force attacks (also in public POSTs)
        $data = $this->request->getJSON(true);

        $model = new UserModel();
        if (!$model->validateLogin($data)) {
            return $this->failValidationErrors($model->getErrors());
        }

        // get username or email
        $field = !empty($data['username']) ? 'username' : 'email';
        $value = $data[$field];

        // search user, if exists
        $user = $model->where($field, $value)->first();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->failValidationErrors(['login' => 'Nombre de usuario o contraseña incorrectos']);
        }

        $token = $this->generateJWT($user);
        return $this->respond([
                'message' => 'Login exitoso',
                'token' => $token,
                'user'    => [
                    // 'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                ],
        ]);
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

    protected function generateJWT(array $user): string
    {
        $key = getenv('JWT_SECRET');
        $payload = [
            'id' => $user['id'],
            'role' => $user['role'],
            // 'iss' => 'http://domain.com', // Emisor del token
            // 'aud' => 'http://domain.com', // Audiencia del token
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
            'data' => [
                // 'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                // 'role'     => $user['role'],
            ],
        ];
        return JWT::encode($payload, $key, 'HS256');
    }
}
