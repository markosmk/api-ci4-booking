<?php

namespace App\Controllers;

use App\Controllers\ResourceBaseController;

class AnalyticsController extends ResourceBaseController
{
    public function index()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'dashboard_stats';

        // try to get data from cache
        $cachedData = $cache->get($cacheKey);

        if ($cachedData) {
            return $this->response->setJSON($cachedData);
        }

        $db = \Config\Database::connect();

        $currentMonth = date('Y-m');
        $currentDate = date('Y-m-d');
        $currentMonthStart = date('Y-m-01');
        // Inicio del mes anterior
        $previousMonthStart = date('Y-m-01', strtotime('first day of last month'));

        // Calcular el mismo día del mes anterior
        $currentDay = (int)date('d'); // Día actual
        $daysInPreviousMonth = (int)date('t', strtotime($previousMonthStart)); // Días del mes anterior
        $adjustedDay = min($currentDay, $daysInPreviousMonth); // Ajustar al último día del mes anterior si necesario
        $previousMonthEnd = date('Y-m-', strtotime($previousMonthStart)) . str_pad($adjustedDay, 2, '0', STR_PAD_LEFT);

        // Total ingresos actuales (mes en curso)
        $currentIncome = $db->table('bookings')
            ->selectSum('totalPrice', 'total')
            ->where('status', 'CONFIRMED')
            ->where('created_at >=', $currentMonthStart)
            ->where('created_at <=', $currentDate)
            ->get()
            ->getRow()
            ->total ?? 0;

        // Total ingresos del mes anterior (hasta el mismo día ajustado)
        $lastIncome = $db->table('bookings')
            ->selectSum('totalPrice', 'total')
            ->where('status', 'CONFIRMED')
            ->where('created_at >=', $previousMonthStart)
            ->where('created_at <=', $previousMonthEnd)
            ->get()
            ->getRow()
            ->total ?? 0;

        $incomeChange = $lastIncome > 0
        ? (($currentIncome - $lastIncome) / $lastIncome) * 100
        : null;


        // ********* quantity of visitors ********* //
        $currentVisitors = $db->table('bookings')
        ->selectSum('quantity', 'total')
        ->where('status', 'CONFIRMED')
        ->where('created_at >=', $currentMonthStart)
        ->where('created_at <=', $currentDate)
        ->get()
        ->getRow()
        ->total ?? 0;

        $lastVisitors = $db->table('bookings')
            ->selectSum('quantity', 'total')
            ->where('status', 'CONFIRMED')
            ->where('created_at >=', $previousMonthStart)
            ->where('created_at <=', $previousMonthEnd)
            ->get()
            ->getRow()
            ->total ?? 0;

        $visitorsChange = $lastVisitors > 0
        ? (($currentVisitors - $lastVisitors) / $lastVisitors) * 100
        : null;

        // ********* quantity of bookings ********* //
        $currentBookings = $db->table('bookings')
        ->where('status', 'CONFIRMED')
        ->where('created_at >=', $currentMonthStart)
        ->where('created_at <=', $currentDate)
        ->countAllResults() ?? 0;

        $lastBookings = $db->table('bookings')
            ->where('status', 'CONFIRMED')
            ->where('created_at >=', $previousMonthStart)
            ->where('created_at <=', $previousMonthEnd)
            ->countAllResults() ?? 0;

        $bookingsChange = $lastBookings > 0
        ? (($currentBookings - $lastBookings) / $lastBookings) * 100
        : null;

        // ********* total Bookings PENDING and CONFIRMED ********* //
        $totalBookingsConfirmed = $db->table('bookings')
            ->where('status', 'CONFIRMED')
            ->countAllResults();

        $totalBookingsPending = $db->table('bookings')
            ->where('status', 'PENDING')
            ->countAllResults();

        // ********* popular tour ********* //
        $popularTour = $db->table('bookings')
        ->select('tourId, COUNT(*) as bookings_count, tours.name as tour_name')
        ->join('tours', 'tours.id = bookings.tourId')
        ->where('status', 'CONFIRMED')
        ->where("DATE_FORMAT(tours.created_at, '%Y-%m')", $currentMonth)
        ->groupBy('tourId')
        ->orderBy('bookings_count', 'DESC')
        ->limit(1)
        ->get()
        ->getRowArray();



        // monthly income by months
        $monthlyIncome = $db->table('bookings')
            ->select("MONTH(created_at) as month, SUM(totalPrice) as income")
            ->where('status', 'CONFIRMED')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'income' => [
                'total' => $currentIncome,
                'change' => $incomeChange !== null ? number_format($incomeChange, 1) . '%' : 'N/A',
            ],
            'visitors' => [
                'total' => $currentVisitors,
                'change' => $visitorsChange !== null ? number_format($visitorsChange, 1) . '%' : 'N/A',
            ],
            'bookings' => [
                'total' => $currentBookings,
                'change' => $bookingsChange !== null ? number_format($bookingsChange, 1) . '%' : 'N/A',
            ],
            'total_bookings_confirmed' => $totalBookingsConfirmed ?? 0,
            'total_bookings_pending' => $totalBookingsPending ?? 0,
            'popularTour' => $popularTour,
            'monthly_income' => $this->formatMonthlyIncome($monthlyIncome),
        ];

        // save in cache by 10 minutes (600 seconds)
        $cache->save($cacheKey, $data, 600);

        return $this->response->setJSON($data);
    }

    public function recentsBookings()
    {
        $db = \Config\Database::connect();
        $bookings = $db->table('bookings')
            ->select('
                bookings.id AS booking_id,
                bookings.quantity,
                bookings.status,
                bookings.created_at AS booking_created_at,
                customers.name AS customer_name,
                tours.name AS tour_name
                ')
            ->join('customers', 'customers.id = bookings.customerId')
            ->join('tours', 'tours.id = bookings.tourId')
            // ->where('bookings.status', 'PENDING')
            ->orderBy('bookings.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($bookings);
    }

    public function clearCache()
    {
        $cache = \Config\Services::cache();
        $cache->clean();
        return $this->response->setJSON(['message' => 'Cache cleared']);
    }

    private function formatMonthlyIncome(array $data)
    {
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $months = array_fill_keys($monthNames, 0);
        foreach ($data as $row) {
            $monthIndex = (int)$row['month'] - 1; // month index in SQL starts from 0
            $monthName = $monthNames[$monthIndex];
            $months[$monthName] = (float)$row['income'];
        }

        return $months;
    }
}