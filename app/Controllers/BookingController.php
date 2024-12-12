<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;
use App\Models\BookingModel;
use App\Models\ScheduleModel;
use App\Models\TourModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;

class BookingController extends ResourceBaseController
{
    protected $modelName = BookingModel::class;
    protected $format    = 'json';
    protected $db;
    protected $scheduleModel;
    protected $tourModel;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->tourModel = new TourModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage') ?? 20;

        $bookings = $this->model->getBookingsWithDetails($page, $perPage);
        $totalBookings = $this->model->countAllBookingsWithDetails();

        return $this->respond([
            'results' => $bookings,
            'pagination' => [
                'currentPage' => (int)$page,
                'perPage' => (int)$perPage,
                'totalItems' => $totalBookings,
                'totalPages' => ceil($totalBookings / $perPage)
            ]
        ]);
    }

    public function show($id = null)
    {
        sleep(2);
        $booking = $this->model->getBookingById($id);

        if (!$booking) {
            return $this->failNotFound('Booking not found');
        }

        return $this->respond($booking);
    }

    public function createBookingTour()
    {
        $data = $this->request->getJSON(true);

        $rules = [
            'tourId'     => 'required|is_natural_no_zero',
            'scheduleId' => 'required|is_natural_no_zero',
            'quantity'   => 'required|integer',
            'name'       => 'required|min_length[3]|max_length[100]',
            'phone'      => 'required|min_length[8]|max_length[200]',
            'email'      => 'required|valid_email|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $this->db->transStart();

        try {
            // 1) verifica si el horario está disponible
            $schedule = $this->scheduleModel->find($data['scheduleId']);

            if (!$schedule || $schedule['active'] == 0) {
                return $this->failValidationErrors(['error' => 'El horario seleccionado no esta disponible']);
            }

            // 2) verifica si hay capacidad para ese horario
            $tour = $this->tourModel->find($data['tourId']);

            // Calcular las reservas actuales para este schedule
            $currentBookings = $this->model->where('scheduleId', $data['scheduleId'])->findAll();
            $totalQuantity = array_sum(array_column($currentBookings, 'quantity'));

            // Verificar si la cantidad de personas excede la capacidad del tour
            if ($totalQuantity + $data['quantity'] > $tour['capacity']) {
                return $this->failValidationErrors(['error' => 'La cantidad de personas excede la capacidad del tour']);
            }

            // 3) create customer
            $customerData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ];

            $customerModel = new \App\Models\CustomerModel();
            $customerId = $customerModel->insert($customerData);

            // 4) create booking
            $bookingData = [
                'customerId' => $customerId,
                'tourId'     => $data['tourId'],
                'scheduleId' => $data['scheduleId'],
                'quantity'   => $data['quantity'],
                'status'     => 'PENDING',
                'totalPrice' => $tour['price'] * $data['quantity'],
                'tourData' => json_encode([
                    'name' => $tour['name'],
                    'content' => $tour['content'],
                    'duration' => $tour['duration'],
                    'capacity' => $tour['capacity'],
                    'price' => $tour['price'],
                    'last_updated' => $tour['updated_at']
                ]),
                'scheduleData' => json_encode([
                    'date' => $schedule['date'],
                    'startTime' => $schedule['startTime'],
                    'endTime' => $schedule['endTime'],
                    'last_updated' => $tour['updated_at']
                ]),
                'customerData' => json_encode([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone']
                ]),
            ];

            $this->model->insert($bookingData);


            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error al crear la reserva');
            }

            return $this->respondCreated(['message' => 'Tour confirmado con éxito']);
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            return $this->failValidationErrors(['error' => 'Error DB booking: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->failValidationErrors(['error' => 'Error booking: ' . $e->getMessage()]);
        }

    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        // TODO: validate data

        $booking = $this->model->find($id);
        if (!$booking) {
            return $this->failNotFound('Reserva no encontrada');
        }

        if ($this->model->update($id, $data)) {
            // if status changed then emit event
            if ($booking['status'] !== $data['status']) {
                Events::trigger('bookingUpdated');
            }
            return $this->respondUpdated(['message' => 'Reserva actualizada con exito']);
        }

        return $this->failServerError('No se pudo actualizar la reserva');
    }

    public function updateBookingStatus($bookingId = null)
    {
        if (empty($bookingId)) {
            return $this->failNotFound('Missing booking id');
        }

        $data = $this->request->getJSON(true);
        $allowedStatuses = ['CONFIRMED', 'PENDING', 'CANCELED'];

        // $db = db_connect();
        // log_message('info', print_r($data, true));
        if (!in_array($data['status'], $allowedStatuses)) {
            return $this->failValidationErrors('El Estado es incorrecto.');
        }

        $dataToUpdate = [
            'status' => $data['status']
        ];

        // validar totalPrice, si existe, y si es un FLOAT valido 10,2
        if (isset($data['totalPrice'])) {
            if (!is_numeric($data['totalPrice'])) {
                return $this->failValidationErrors('Precio Total debe ser numerico.');
            }
            $dataToUpdate['totalPrice'] = number_format((float)$data['totalPrice'], 2, '.', '');
        }

        //TODO: enviar mail al cliente para avisar que se confirmo su reserva. un email automatica con la info del tour y la reserva
        // con un checkbox, si el usuario lo activa comprobamos aqui y se envia el correo.

        log_message('info', print_r($dataToUpdate, true));

        if ($this->model->update($bookingId, $dataToUpdate)) {
            Events::trigger('bookingUpdated');
            return $this->respondUpdated(['message' => 'Booking status updated']);
        }

        return $this->failServerError('Error updating booking status');
    }

}
