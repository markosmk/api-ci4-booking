<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class ErrorController extends ResourceController
{
    public function notFound()
    {
        return $this->failNotFound('Ruta No Encontrada.');
    }
}
