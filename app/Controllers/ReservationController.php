<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class ReservationController extends ResourceBaseController
{
    protected $modelName = ReservationModel::class;
    protected $format    = 'json';

    public function index()
    {
        $reservations = $this->model->getReservationsWithType();
        return $this->respond($reservations);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->validateData($data)) {
            return $this->failValidationErrors($this->model->getErrors());
        }

        // Validate manual reservation_type
        $reservationTypeModel = new \App\Models\ReservationTypeModel();
        if (!$reservationTypeModel->find($data['reservation_type'])) {
            return $this->failValidationErrors(['reservation_type' => 'El tipo de reserva no es válido']);
        }

        $reservationData = $this->model->cleanData($data);

        try {
            if (!$this->model->insert($reservationData)) {
                return $this->failValidationErrors($this->model->errors());
            }
            return $this->respondCreated(['message' => 'Reserva creada con éxito']);
        } catch (DatabaseException $e) {
            log_message('error', 'Error de base de datos: ' . $e->getMessage());
            return $this->respondWithServerError('Ocurrió un problema al procesar su solicitud. Por favor, inténtelo más tarde.');
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->respondWithServerError('No se pudo crear la reserva');
        }

    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $this->model->setValidationRules([
            'status' => 'in_list[pending,completed,canceled]',
            'notes'  => 'permit_empty|max_length[500]',
        ]);

        // verify reservation
        $reservation = $this->model->find($id);
        if (!$reservation) {
            return $this->failNotFound('Reserva no encontrada');
        }

        // update only status and notes
        $updateData = [];
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        try {
            if (!$this->model->update($id, $updateData)) {
                return $this->failValidationErrors($this->model->errors());
            }
            return $this->respondUpdated(['message' => 'Reserva actualizada con éxito']);
        } catch (DatabaseException $e) {
            log_message('error', 'Error de base de datos: ' . $e->getMessage());
            return $this->respondWithServerError('Ocurrió un problema al procesar su solicitud. Por favor, inténtelo más tarde.');
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->respondWithServerError('No se pudo actualizar la reserva');
        }

    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Reserva eliminada con éxito']);
        }

        return $this->failServerError('No se pudo eliminar la reserva');
    }
}