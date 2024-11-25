<?php

namespace App\Filters;

// use App\Libraries\CustomRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface | IncomingRequest $request, $arguments = null)
    {
        $token = $request->getCookie('app_token');

        // if token not found in cookie then check header
        if (!$token) {
            $authHeader = $request->getHeaderLine('Authorization');
            if (!$authHeader) {
                return service('response')->setJSON([
                    'error'   => true,
                    'message' => 'No tienes autorización para acceder a este recurso'
                ])->setStatusCode(401);
            }

            // take the token
            $token = str_replace('Bearer ', '', $authHeader);
        }

        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // save data user in request for future use
            // TODO: adjust user in IncomingRequest
            $request->user = (array) $decoded;
        } catch (\Exception $e) {
            return service('response')->setJSON([
                'error' => true,
                'message' => $e->getMessage()
            ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // not action required
    }
}