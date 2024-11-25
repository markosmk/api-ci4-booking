<?php

namespace App\Controllers;

// use CodeIgniter\RESTful\ResourceController;
use App\Models\ReservationTypeModel;
use App\Controllers\ResourceBaseController;

class ReservationTypeController extends ResourceBaseController
{
    protected $modelName = ReservationTypeModel::class;
    protected $format    = 'json';

    public function index()
    {
        $types = $this->model->findAll();
        return $this->respond($types);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['name'])) {
            return $this->failValidationErrors('El nombre del tipo de reserva es obligatorio');
        }

        $typeData = [
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
        ];

        if ($this->model->insert($typeData)) {
            return $this->respondCreated(['message' => 'Tipo de reserva creado con éxito']);
        }

        return $this->failServerError('No se pudo crear el tipo de reserva');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        // verify reservation
        $reservation = $this->model->find($id);
        if (!$reservation) {
            return $this->failNotFound('Tipo de Reserva no encontrada');
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }

        if ($this->model->update($id, $updateData)) {
            return $this->respondUpdated(['message' => 'Tipo de Reserva actualizada con éxito']);
        }

        return $this->failServerError('No se pudo actualizar el tipo de reserva');
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Tipo de reserva eliminado con éxito']);
        }

        return $this->failServerError('No se pudo eliminar el tipo de reserva');
    }
}
