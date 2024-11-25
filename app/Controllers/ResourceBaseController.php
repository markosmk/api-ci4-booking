<?php

namespace App\Controllers;

use App\Libraries\CustomRequest;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Extends ResourceController to add common functionality to all RESTful controllers
 *
 * @property CLIRequest|CustomRequest $request
 */
abstract class ResourceBaseController extends ResourceController
{
    protected $request;

    protected $helpers = ['cookie'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Call to ResourceController::initController()
        parent::initController($request, $response, $logger);

        // load helpers
        helper($this->helpers);
    }

    /**
     * Method to respond with a server error
     */
    protected function respondWithServerError($message = 'Ha ocurrido un error en el servidor')
    {
        return $this->response->setStatusCode(500)->setJSON([
            'error'   => true,
            'message' => $message,
        ]);
    }

    protected function respondWithError($message = 'Ha ocurrido un error', $code = 500)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'error'   => true,
            'message' => $message,
        ]);
    }
}