<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;
use App\Models\SettingsModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class SettingsController extends ResourceBaseController
{
    protected $modelName = SettingsModel::class;
    protected $format    = 'json';

    public function index()
    {
        try {
            $settings = $this->model->find("1");
            return $this->respond($settings);
        } catch (DatabaseException $e) {
            return $this->failServerError('Database error: ' . $e->getMessage());
        }
    }

    public function update($id = null)
    {
        sleep(1);
        $data = $this->request->getJSON(true);

        if (!$id && $id != 1) {
            return $this->failNotFound('Settings not found');
        }

        if (!$this->model->validateData($data)) {
            return $this->failValidationErrors($this->model->getErrors());
        }

        if ($this->model->update($id, $data)) {
            return $this->respondUpdated(['message' => 'Settings updated successfully']);
        }

        return $this->failValidationErrors($this->model->errors());
    }

}