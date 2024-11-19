<?php

namespace App\Filters;

// use App\Libraries\CustomRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {

        // if (!$request instanceof CustomRequest) {
        //     throw new \RuntimeException('Request debe ser una instancia de CustomRequest');
        // }

        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return service('response')->setJSON(['message' => 'Token no proporcionado'])->setStatusCode(401);
        }

        // extract token from header
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // save data user in request for future use
            $request->user = (array) $decoded;

        } catch (\Exception $e) {
            return service('response')->setJSON([
                'message' => 'Token inválido o expirado', 
                'error' => $e->getMessage()
            ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // not action required
    }
}