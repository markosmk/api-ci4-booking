<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table            = 'schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = ['tourId', 'date', 'startTime', 'endTime', 'active'];

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
        'tourId'    => 'required|integer',
        'date'      => 'required|valid_date',
        'startTime' => 'required|valid_time', // has a customRule
        'endTime'   => 'required|valid_time',
        'active'  => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'tourId'    => [
            'required' => 'El campo tourId es requerido.',
            'integer'  => 'El campo tourId debe ser un entero.',
        ],
        'date'      => [
            'required' => 'El campo date es requerido.',
            'valid_date' => 'El formato de date debe ser yyyy-mm-dd.',
        ],
        'startTime' => [
            'required' => 'El campo startTime es requerido.',
            'valid_time' => 'El formato de startTime debe ser hh:mm:ss.',
        ],
        'endTime' => [
            'required' => 'El campo endTime es requerido.',
            'valid_time' => 'El formato de endTime debe ser hh:mm:ss.',
        ],
        'active' => [
            'in_list' => 'El campo active debe ser 0 o 1.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;


    public function getAvailabilityRange($tourId)
    {
        $schedules = $this->where('tourId', $tourId)->findAll();

        if (empty($schedules)) {
            return null;
        }

        $dates = array_column($schedules, 'date');

        $minDate = min($dates);  // more old
        $maxDate = max($dates);

        return [
            'dateFrom' => $minDate,
            'dateTo' => $maxDate,
        ];
    }
}
