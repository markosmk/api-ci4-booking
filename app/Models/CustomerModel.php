<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'phone', 'email', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // active protection of data
    protected $returnType = 'array';


    protected $validationRules = [
        'name'  => 'required|min_length[3]|max_length[100]',
        'phone' => 'required|min_length[8]|max_length[200]',
        'email' => 'required|valid_email|max_length[150]',
    ];

    protected $validationMessages = [
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

    /**
     * Find user by phone
     * ex: $model->findByPhone('54996065')
     *
     * @param string $phone
     * @return array|null
     */
    public function findByPhone(string $phone)
    {
        return $this->where('phone', $phone)->first();
    }

    /** not used, because need reformat in app */
    public function getCustomersWithBookings(int $page = 1, int $perPage = 20)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $builder->select('
            customers.id AS customer_id,
            customers.name AS customer_name,
            customers.email AS customer_email,
            bookings.id AS booking_id,
            bookings.tourId AS booking_tour_id,
            bookings.scheduleId AS booking_schedule_id,
            bookings.quantity AS booking_quantity,
            bookings.status AS booking_status,
        ');
        $builder->join('bookings', 'customers.id = bookings.customerId', 'left');
        $builder->orderBy('customers.id');

        $offset = ($page - 1) * $perPage;
        $builder->limit($perPage, $offset);

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getCustomersWithCount(int $page = 1, int $perPage = 20)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $builder->select('
            customers.*,
            COUNT(bookings.id) AS total_bookings
        ');
        $builder->join('bookings', 'customers.id = bookings.customerId', 'left');
        $builder->groupBy('customers.id');
        $builder->orderBy('customers.created_at', 'desc');

        $offset = ($page - 1) * $perPage;
        $builder->limit($perPage, $offset);

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function countAllCustomers()
    {
        return $this->countAllResults();
    }
}