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
            return service('response')
                ->setJSON([
                    'error'   => true,
                    'message' => 'Acceso denegado. No tienes autorización para realizar estaacción.',
                ])
                ->setStatusCode(401);
        }

        // verify user is authenticated, and is superadmin
        if ($user['role'] !== 'superadmin') {
            return service('response')
                ->setJSON([
                    'error'   => true,
                    'message' => 'Acceso denegado. No tienes permiso para realizar esta acción.',
                ])
                ->setStatusCode(403);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}
