<?php

namespace App\Models;

use CodeIgniter\Model;

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

    /**
     * Get all reservations with their corresponding reservation type.
     * ex: $model->getReservationsWithType()
     * 
     * @return array
     */
    public function getReservationsWithType()
    {
        return $this->select('reservations.*, reservation_types.name as reservation_type_name')
                    ->join('reservation_types', 'reservations.reservation_type = reservation_types.id')
                    ->findAll();
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