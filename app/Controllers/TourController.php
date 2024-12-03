<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;
use App\Models\TourModel;
use App\Models\ScheduleModel;
use App\Models\BookingModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class TourController extends ResourceBaseController
{
    protected $modelName = TourModel::class;
    protected $format    = 'json';

    protected $scheduleModel;
    protected $bookingModel;
    protected $db;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->bookingModel = new BookingModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // TODO: add pagination
        $tours = $this->model->orderBy('created_at', 'desc')->findAll();
        return $this->respond($tours);
    }

    public function show($id = null)
    {
        $tour = $this->model->find($id);
        if (!$tour) {
            return $this->failNotFound('Tour not found');
        }

        $availabilityRange = $this->scheduleModel->getAvailabilityRange($id);

        // Obtener el mes actual
        // $currentMonth = date('m');
        // $currentYear = date('Y');

        $schedules = $this->scheduleModel
                            ->where('tourId', $id)
                            // ->where('MONTH(date)', $currentMonth)
                            // ->where('YEAR(date)', $currentYear)
                            ->findAll();

        // calcule availability for each schedule
        foreach ($schedules as &$schedule) {
            // get current bookings
            $currentBookings = $this->bookingModel->where('scheduleId', $schedule['id'])->findAll();
            // sum all quantity
            $totalBooked = array_sum(array_column($currentBookings, 'quantity'));
            // calculate availability
            $schedule['available'] = $tour['capacity'] - $totalBooked;
        }

        return $this->respond([
            'tour' => $tour,
            'schedules' => $schedules,
            'availability' => $availabilityRange,
        ]);
    }

    public function create()
    {

        $data = $this->request->getJSON(true);

        $this->model->setValidationRules([
            'name'        => 'required',
            'description' => 'required',
            'duration'    => 'required|integer', // minutes
            'capacity'    => 'required|integer',
            // optionals
            'dateFrom'    => 'permit_empty|valid_date',
            'dateTo'      => 'permit_empty|valid_date',
            'startTime'   => 'permit_empty|valid_time',
            'endTime'     => 'permit_empty|valid_time',
            'weekends'    => 'permit_empty|in_list[0,1]',
        ]);

        if (!$this->model->validate($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        // Si se proporcionan dateFrom y dateTo, asegúrate de que los otros campos estén presentes
        if (!empty($data['dateFrom']) || !empty($data['dateTo'])) {
            if (empty($data['startTime']) || empty($data['endTime']) || !isset($data['weekends'])) {
                return $this->failValidationErrors(['error' => 'startTime, endTime, and weekends are required when dateFrom or dateTo is provided.']);
            }
        }



        $this->db->transStart();

        try {

            $tourId = $this->model->insert($data);

            // Solo crear schedules si dateFrom y dateTo están presentes
            if (!empty($data['dateFrom']) && !empty($data['dateTo'])) {
                // Crear schedules
                $dateFrom = new \DateTime($data['dateFrom']);
                $dateTo = new \DateTime($data['dateTo']);
                $duration = $data['duration']; // En minutos
                $startTime = new \DateTime($data['startTime']);
                $endTime = new \DateTime($data['endTime']);

                // Iterar sobre cada día entre dateFrom y dateTo
                while ($dateFrom <= $dateTo) {
                    // Si weekends es false, omitir sábados y domingos
                    if ($data['weekends'] == 0 && ($dateFrom->format('N') == 6 || $dateFrom->format('N') == 7)) {
                        $dateFrom->modify('+1 day');
                        continue;
                    }

                    // Calcular horarios
                    $currentStartTime = clone $startTime;
                    while ($currentStartTime <= $endTime) {
                        // Calcular el endTime del schedule basado en la duración
                        $currentEndTime = clone $currentStartTime;
                        $currentEndTime->modify("+{$duration} minutes");

                        // Verificar que el endTime del schedule no exceda el endTime permitido
                        if ($currentEndTime > $endTime) {
                            break;
                        }

                        $scheduleData = [
                            'tourId' => $tourId,
                            'date' => $dateFrom->format('Y-m-d'),
                            'startTime' => $currentStartTime->format('H:i:s'),
                            'endTime' => $currentEndTime->format('H:i:s'),
                            'active' => 1,
                        ];

                        // save  el schedule y verificar si se inserta correctamente
                        if (!$this->scheduleModel->insert($scheduleData)) {
                            throw new \Exception('Failed to create schedule: ' . json_encode($this->scheduleModel->errors()));
                        }

                        // Avanzar al siguiente horario
                        $currentStartTime->modify("+{$duration} minutes");
                    }

                    // Avanzar al siguiente día
                    $dateFrom->modify('+1 day');
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->respondCreated(['message' => 'Tour creado con éxito']);

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            return $this->failValidationErrors(['error' => 'Error DB tour: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->failValidationErrors(['error' => 'Error tour: ' . $e->getMessage()]);
        }
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        //TODO: validate data
        $tour = $this->model->find($id);
        if (!$tour) {
            return $this->failNotFound('Tour not found');
        }

        $message = 'Tour actualizado';
        if ($tour['price'] !== $data['price']) {
            $message = 'El precio actualizado solo se aplicará a nuevas reservas. Las reservas existentes mantendrán el precio original.';
        }
        log_message('info', print_r($data, true));
        if ($this->model->update($id, $data)) {
            return $this->respondUpdated(['message' => $message]);
        }

        return $this->failValidationErrors($this->model->errors());
    }

}