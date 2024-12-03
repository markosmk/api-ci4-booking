<?php

namespace App\Filters;

// use App\Libraries\CustomRequest;

use App\Libraries\CustomRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthFilter implements FilterInterface
{
    /**
     * Does the actual filtering
     *
     * @param CustomRequest $request
     * @param array|null $arguments
     */
    public function before(RequestInterface | IncomingRequest $request, $arguments = null)
    {
        $token = $request->getCookie('app_token');

        // if token not found in cookie then check header
        if (!$token) {

            $authHeader = $request->getHeaderLine('Authorization');
            if (!$authHeader) {
                return $this->unauthorizedResponse('No tienes autorización para acceder a este recurso');
            }
            //     return service('response')->setJSON([
            //         'error'   => true,
            //         'message' => 'No tienes autorización para acceder a este recurso'
            //     ])->setStatusCode(401);
            // }

            // take the token
            $token = str_replace('Bearer ', '', $authHeader);
        }

        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // save data user in request for future use
            $request->user = (array) $decoded;
        } catch (\Exception $e) {

            return $this->unauthorizedResponse($e->getMessage());
            // return service('response')->setJSON([
            //     'error' => true,
            //     'message' => $e->getMessage()
            // ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // not action required
    }

    private function unauthorizedResponse(string $message)
    {
        $response = service('response');

        // Agrega los encabezados CORS a la respuesta
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setStatusCode(401);

        return $response->setJSON([
            'error' => true,
            'message' => $message
        ]);
    }
}