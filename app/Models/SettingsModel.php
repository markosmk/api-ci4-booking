<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['email', 'maxBookingDate', 'phoneWhatsapp', 'aditionalNote', 'termsAndConditions', 'active', 'messageDisabled'];

    // Dates
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'email'              => 'required|valid_email|max_length[150]',
        'phoneWhatsapp'      => 'required|min_length[8]|max_length[20]',
        'aditionalNote'      => 'required|min_length[10]|max_length[200]',
        'termsAndConditions' => 'permit_empty|min_length[10]|max_length[1000]',
        'maxBookingDate'     => 'permit_empty|integer',
        'messageDisabled'    => 'permit_empty|min_length[10]|max_length[200]',
        'active'             => 'permit_empty|in_list[0,1]',
    ];
    // mensajes de validacion en español
    protected $validationMessages = [
        'email'              => [
            'required' => 'El correo electronico es requerido',
            'valid_email' => 'El correo electronico debe ser valido',
            'max_length' => 'El correo electronico no debe exceder los 150 caracteres',
        ],
        'phoneWhatsapp'      => [
            'required' => 'El numero de WhatsApp es requerido',
            'min_length' => 'El numero de WhatsApp debe tener al menos 8 digitos',
            'max_length' => 'El numero de WhatsApp debe tener maximo 20 digitos',
        ],
        'aditionalNote'      => [
            'required' => 'La nota adicional es requerida',
            'min_length' => 'La nota adicional debe tener al menos 10 caracteres',
            'max_length' => 'La nota adicional debe tener maximo 200 caracteres',
        ],
        'termsAndConditions' => [
            'min_length' => 'Los t&eacute;rminos y condiciones deben tener al menos 10 caracteres',
            'max_length' => 'Los t&eacute;rminos y condiciones deben tener maximo 1000 caracteres',
        ],
        'maxBookingDate'     => [
            'integer' => 'La fecha maxima de reserva debe ser un numero entero',
        ],
        'messageDisabled'    => [
            'min_length' => 'El mensaje desactivado debe tener al menos 10 caracteres',
            'max_length' => 'El mensaje desactivado debe tener maximo 200 caracteres',
        ],
        'active'             => [
            'in_list' => 'El campo activo debe ser 0 o 1',
        ],
    ];
    protected $errors = [];

    /**
     * Get errors.
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

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
}