<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

class CustomRequest extends IncomingRequest
{
    /**
     * Almacena los datos del usuario autenticado
     * @var array|null
     */
    public $user;
}