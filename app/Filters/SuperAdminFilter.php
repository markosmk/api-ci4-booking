<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class SuperAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // get user from request (AuthFilter)
        $user = $request->user ?? null;

        // verify user is authenticated
        if (!$user) {
            return $this->unauthorizedResponse('Acceso denegado. No tienes autorización para acceder a este recurso.', 401);
            // return service('response')
            //     ->setJSON([
            //         'error'   => true,
            //         'message' => 'Acceso denegado. No tienes autorización para realizar estaacción.',
            //     ])
            //     ->setStatusCode(401);
        }

        // verify user is authenticated, and is superadmin
        if ($user['role'] !== 'superadmin') {

            return $this->unauthorizedResponse('Acceso denegado. No tienes autorización para realizar esta acción.', 403);
            // return service('response')
            //     ->setJSON([
            //         'error'   => true,
            //         'message' => 'Acceso denegado. No tienes permiso para realizar esta acción.',
            //     ])
            //     ->setStatusCode(403);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }

    private function unauthorizedResponse(string $message, int $statusCode = 401)
    {
        $response = service('response');

        // Agrega los encabezados CORS a la respuesta
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setStatusCode($statusCode);

        return $response->setJSON([
            'error' => true,
            'message' => $message
        ]);
    }
}