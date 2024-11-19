<?php 
namespace App\Controllers;

use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\Response;

class AuthController extends BaseController
{
    public function login()
    {
        $model = new UserModel();
        $data = $this->request->getPost();

        $user = $model->where('username', $data['username'])->first();
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->response->setJSON(['message' => 'Credenciales inválidas'])->setStatusCode(401);
        }

        $key = getenv('JWT_SECRET');
        $payload = [
            'id' => $user['id'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600, // 1 hora de validez
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->response->setJSON(['token' => $token]);
    }

    public function me()
    {
        $authHeader = $this->request->getHeader('Authorization');
        $token = str_replace('Bearer ', '', $authHeader->getValue());

        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $this->response->setJSON((array) $decoded);
        } catch (\Exception $e) {
            return $this->response->setJSON(['message' => 'Token inválido'])->setStatusCode(401);
        }
    }
}