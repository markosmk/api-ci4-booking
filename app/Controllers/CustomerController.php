<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;
use App\Models\CustomerModel;
use App\Models\ScheduleModel;
use App\Models\BookingModel;

class CustomerController extends ResourceBaseController
{
    protected $modelName = CustomerModel::class;
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
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage') ?? 20;

        $customers = $this->model->getCustomersWithCount($page, $perPage);
        $totalCustomers = $this->model->countAllCustomers();

        return $this->respond([
            'results' => $customers,
            'pagination' => [
                'currentPage' => (int)$page,
                'perPage' => (int)$perPage,
                'totalItems' => $totalCustomers,
                'totalPages' => ceil($totalCustomers / $perPage)
            ]
        ]);
    }

    public function show($id = null)
    {
        $customer = $this->model->find($id);
        if (!$customer) {
            return $this->failNotFound('customer not found');
        }

        $bookings = $this->bookingModel
                            ->where('customerId', $id)
                            // ->where('MONTH(date)', $currentMonth)
                            // ->where('YEAR(date)', $currentYear)
                            ->findAll();

        return $this->respond([
            'customer' => $customer,
            'bookings' => $bookings,
        ]);
    }
}