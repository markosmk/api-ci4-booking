<?php

namespace App\Models;

use CodeIgniter\Model;
// use Config\Validation\Validator as Validation;

class ReservationModel extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'customer_name',
        'customer_email',
        'reservation_type',
        'status',
        'notes',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $returnType = 'array';

    protected $validationRules = [
        'customer_name'    => 'required|min_length[3]|max_length[100]',
        'customer_email'   => 'required|valid_email|max_length[150]',
        'reservation_type' => 'required|greater_than[0]',
        'status'           => 'permit_empty|in_list[pending,completed,canceled]',
        'notes'            => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'customer_name' => [
            'required'    => 'El nombre del cliente es obligatorio.',
            'min_length'  => 'El nombre debe tener al menos 3 caracteres.',
            'max_length'  => 'El nombre no puede superar los 100 caracteres.',
        ],
        'customer_email' => [
            'required'    => 'El email del cliente es obligatorio.',
            'valid_email' => 'El email ingresado no es válido.',
            'max_length'  => 'El email no puede superar los 150 caracteres.',
        ],
        'reservation_type' => [
            'required' => 'El tipo de reserva es obligatorio.',
            'greater_than' => 'El tipo de reserva debe ser un número natural mayor que cero.',
            // 'is_natural_no_zero' => 'El tipo de reserva debe ser un número natural mayor que cero.',
        ],
        'status' => [
            'in_list' => 'El estado debe ser uno de los siguientes: pending, completed, canceled.',
        ],
        'notes' => [            
            'max_length' => 'Las notas no pueden superar los 500 caracteres.',
        ],
    ];

    /**
     * Saves the last validation errors.
     * @var array
     */
    protected $errors = [];

    /**
     * Get errors.
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    // Método para limpiar y formatear datos antes de insertarlos
    public function cleanData(array $data): array
    {
        return [
            'customer_name'    => $data['customer_name'],
            'customer_email'   => $data['customer_email'],
            'reservation_type' => $data['reservation_type'],
            'notes'            => $data['notes'] ?? '',
            'status'           => 'pending',
        ];
    }

    /**
     * Valida los datos según las reglas definidas en el modelo.
     *
     * @param array $data Datos a validar.
     * @return bool True si los datos son válidos, False si no lo son.
     */
    public function validateData(array $data): bool
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->validationRules, $this->validationMessages);

        if (!$validation->run($data)) {
            $this->errors = $validation->getErrors(); 
            return false; 
        }

        return true;
    }

    /**
     * Get all reservations with their corresponding reservation type.
     * ex: $model->getReservationsWithType()
     * 
     * @return array
     */
    public function getReservationsWithType()
    {
        return $this->select('reservations.*, reservation_types.name as reservation_type_name, reservation_types.description as reservation_type_description')
        ->join('reservation_types', 'reservations.reservation_type = reservation_types.id', 'left')
        ->findAll();
    }


    public function getReservationsWithTypeFormated(): array
    {
        $db = \Config\Database::connect();

        $query = $db->table($this->table)
            ->select('reservations.*, reservation_types.id as type_id, reservation_types.name as type_name, reservation_types.description as type_description')
            ->join('reservation_types', 'reservation_types.id = reservations.reservation_type', 'left')
            ->get();

        $results = $query->getResultArray();

        // formatted
        return array_map(function ($reservation) {
            return [
                'id'             => $reservation['id'],
                'customer_name'  => $reservation['customer_name'],
                'customer_email' => $reservation['customer_email'],
                'reservation_type' => $reservation['reservation_type'],
                'status'         => $reservation['status'],
                'notes'          => $reservation['notes'],
                'created_at'     => $reservation['created_at'],
                'updated_at'     => $reservation['updated_at'],
                // sure field type
                'type' => $reservation['type_id'] ? [
                    'id'          => $reservation['type_id'],
                    'name'        => $reservation['type_name'],
                    'description' => $reservation['type_description'],
                ] : null,
            ];
        }, $results);
    }

    /**
     * Filter reservations by status
     * ex: $model->filterByStatus('pending')
     * 
     * @param string $status
     * @return array
     */
    public function filterByStatus(string $status)
    {
        return $this->where('status', $status)->findAll();
    }
}