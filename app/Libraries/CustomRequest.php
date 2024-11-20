<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

class CustomRequest extends IncomingRequest
{
    /**
     * save data user in request
     * @var array|null
     */
    public $user;
}