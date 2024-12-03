<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    // TODO: customerID not need login, maybe change to userId
    protected $allowedFields = ['customerId', 'tourId', 'scheduleId', 'quantity', 'status', 'totalPrice', 'tourData', 'scheduleData', 'customerData'];


    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'customerId' => 'required|is_natural_no_zero',
        'tourId'     => 'required|is_natural_no_zero',
        'scheduleId' => 'required|is_natural_no_zero',
        'quantity'   => 'required|integer',
        'status'     => 'in_list[PENDING,CONFIRMED,CANCELED]',
    ];
    protected $validationMessages = [
        'customerId'    => [
            'required' => 'El campo customerId es requerido.',
            'is_natural_no_zero'  => 'El campo customerId debe ser un entero.',
        ],
        'tourId'    => [
            'required' => 'El campo tourId es requerido.',
            'is_natural_no_zero'  => 'El campo tourId debe ser un entero.',
        ],
        'scheduleId' => [
            'required' => 'El campo scheduleId es requerido.',
            'is_natural_no_zero'  => 'El campo scheduleId debe ser un entero.',
        ],
        'quantity'  => [
            'required' => 'El campo quantity es requerido.',
            'integer'  => 'El campo quantity debe ser un entero.',
        ],
        'status'    => [
            'in_list' => 'El campo status debe ser PENDING, CONFIRMED o CANCELED.',
        ],
        // aditionals so for create new booking
        'name' => [
            'required' => 'El campo {field} es requerido',
            'min_length' => 'El campo {field} debe tener al menos {param} caracteres',
            'max_length' => 'El campo {field} debe tener menos de {param} caracteres',
        ],
        'phone' => [
            'required' => 'El campo {field} es requerido',
            'min_length' => 'El campo {field} debe tener al menos {param} caracteres',
            'max_length' => 'El campo {field} debe tener menos de {param} caracteres',
        ],
        'email' => [
            'required' => 'El campo {field} es requerido',
            'valid_email' => 'El campo {field} debe ser un email valido',
            'max_length' => 'El campo {field} debe tener menos de {param} caracteres',
        ]
    ];

    public function getBookingsWithDetails(int $page = 1, int $perPage = 20)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $builder->select('
            bookings.id, bookings.quantity, bookings.status, bookings.totalPrice, bookings.created_at,
            customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone,
            JSON_EXTRACT(bookings.tourData, "$.name") as tour_name,
            JSON_EXTRACT(bookings.tourData, "$.price") as tour_price
        ');

        $builder->join('customers', 'customers.id = bookings.customerId');
        $builder->orderBy('bookings.created_at', 'desc');
        // $builder->join('tours', 'tours.id = bookings.tourId');
        // $builder->join('schedules', 'schedules.id = bookings.scheduleId');

        // implement pagination
        $offset = ($page - 1) * $perPage;
        $builder->limit($perPage, $offset);

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function countAllBookingsWithDetails()
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        // to count all exactly
        $builder->join('customers', 'customers.id = bookings.customerId');
        $builder->join('tours', 'tours.id = bookings.tourId');
        $builder->join('schedules', 'schedules.id = bookings.scheduleId');

        return $builder->countAllResults();
    }

    public function getBookingById(int $id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $builder->select('
            bookings.*,
            tours.name as tour_name, tours.description as tour_description, tours.price as tour_price, tours.content as tour_content, tours.capacity as tour_capacity, tours.updated_at as tour_updated_at,
            schedules.date as schedule_date, schedules.startTime as schedule_start_time, schedules.endTime as schedule_end_time,
            customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone
        ');
        // JSON_EXTRACT(bookings.tourData, "$.name") as tourData_name,
        // JSON_EXTRACT(bookings.tourData, "$.price") as tourData_price,
        // JSON_EXTRACT(bookings.tourData, "$.duration") as tourData_duration,
        // JSON_EXTRACT(bookings.tourData, "$.content") as tourData_content
        $builder->join('customers', 'customers.id = bookings.customerId');
        $builder->join('tours', 'tours.id = bookings.tourId');
        $builder->join('schedules', 'schedules.id = bookings.scheduleId');
        $builder->where('bookings.id', $id);

        $query = $builder->get();
        return $this->transformData($query->getResultArray(), true);
    }

    private function transformData($bookings, $isOne = false)
    {
        if ($bookings == null) {
            $formated = [];
        } else {
            $formated = array_map(function ($booking) {
                return [
                    'id' => $booking['id'],
                    'tourId' => $booking['tourId'],
                    'scheduleId' => $booking['scheduleId'],
                    'customerId' => $booking['customerId'],
                    'status' => $booking['status'],
                    'quantity' => $booking['quantity'],
                    'totalPrice' => $booking['totalPrice'],
                    // take real price from tourData
                    'tourData' => $booking['tourData'] ? json_decode($booking['tourData']) : null,
                    'customer' => [
                        'name' => $booking['customer_name'],
                        'email' => $booking['customer_email'],
                        'phone' => $booking['customer_phone'],
                    ],
                    'tour' => [
                        'name' => $booking['tour_name'],
                        'description' => $booking['tour_description'],
                        // this data may be compared with tourData
                        'content' => $booking['tour_content'],
                        'capacity' => $booking['tour_capacity'],
                        'price' => $booking['tour_price'],
                        'updatedAt' => $booking['tour_updated_at'],
                    ],
                    'schedule' => [
                        'date' => $booking['schedule_date'],
                        'startTime' => $booking['schedule_start_time'],
                        'endTime' => $booking['schedule_end_time'],
                    ],
                    'createdAt' => $booking['created_at'],
                ];
            }, $bookings);
        }

        if ($isOne) {
            return isset($formated[0]) ? $formated[0] : null;
        }
        return $formated;
    }
}