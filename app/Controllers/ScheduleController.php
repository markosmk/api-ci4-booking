<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;
use App\Models\BookingModel;
use App\Models\ScheduleModel;
use App\Models\TourModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class ScheduleController extends ResourceBaseController
{
    protected $modelName = ScheduleModel::class;
    protected $format    = 'json';

    protected $tourModel;
    protected $bookingModel;
    protected $db;

    public function __construct()
    {
        $this->tourModel = new TourModel();
        $this->bookingModel = new BookingModel();
        $this->db = \Config\Database::connect();
    }

    public function showByTourId($tourId = null, $date = null)
    {
        return $this->respond($this->model->where('tourId', $tourId)->where('date', $date)->first());
    }

    public function createSchedule($tourId)
    {
        $data = $this->request->getJSON(true);
        $data['tourId'] = $tourId;

        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Schedule created successfully']);
        } else {
            return $this->failValidationErrors($this->model->errors());
        }

    }

    public function getSchedulesByMonth($tourId, $month, $year)
    {
        // Validar que el tourId, month y year sean números válidos
        if (!is_numeric($tourId) || !is_numeric($month) || !is_numeric($year)) {
            return $this->failValidationErrors(['error' => 'Invalid input']);
        }

        $tour = $this->tourModel->find($tourId);
        if (!$tour) {
            return $this->failNotFound('Tour not found');
        }

        $schedules = $this->model
                          ->where('tourId', $tourId)
                          ->where('MONTH(date)', $month)
                          ->where('YEAR(date)', $year)
                          ->findAll();

        foreach ($schedules as &$schedule) {
            // get current bookings
            $currentBookings = $this->bookingModel->where('scheduleId', $schedule['id'])->findAll();
            // sum all quantity
            $totalBooked = array_sum(array_column($currentBookings, 'quantity'));
            // calculate availability
            $schedule['available'] = $tour['capacity'] - $totalBooked;
        }

        return $this->respond(['schedules' => $schedules]);
    }

    public function getSchedulesByDate($tourId, $date)
    {
        sleep(1);
        // Validar que el tourId sea un número válido
        if (!is_numeric($tourId)) {
            return $this->failValidationErrors(['error' => 'Invalid tour ID']);
        }

        // Validar la fecha en el formato correcto (puedes usar una expresión regular o DateTime)
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateTime) {
            return $this->failValidationErrors(['error' => 'Invalid date format. Use YYYY-MM-DD']);
        }

        $tour = $this->tourModel->find($tourId);
        if (!$tour) {
            return $this->failNotFound('Tour not found');
        }

        // Obtener schedules del tour específico para una fecha concreta
        $schedules = $this->model
                          ->where('tourId', $tourId)
                          ->where('date', $date)
                          ->findAll();

        // Calcular disponibilidad para cada schedule
        foreach ($schedules as &$schedule) {
            // Obtener reservas actuales
            $currentBookings = $this->bookingModel->where('scheduleId', $schedule['id'])->findAll();
            // Sumar todas las cantidades
            $totalBooked = array_sum(array_column($currentBookings, 'quantity'));
            // Calcular disponibilidad
            $schedule['available'] = $tour['capacity'] - $totalBooked;
        }

        return $this->respond(['schedules' => $schedules]);
    }

}