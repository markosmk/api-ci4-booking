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

        $totalIncome = $db->table('bookings')
            ->selectSum('totalPrice', 'total_income')
            ->where('status', 'CONFIRMED')
            ->get()
            ->getRow()
            ->total_income;

        $totalVisitors = $db->table('bookings')
            ->selectSum('quantity', 'total_visitors')
            ->where('status', 'CONFIRMED')
            ->get()
            ->getRow()
            ->total_visitors;

        $totalBookingsConfirmed = $db->table('bookings')
            ->where('status', 'CONFIRMED')
            ->countAllResults();

        $totalBookingsNotPending = $db->table('bookings')
            ->where('status !=', 'PENDING')
            ->countAllResults();

        $totalActiveTours = $db->table('tours')
            ->where('active', '1')
            ->countAllResults();

        // monthly income by months
        $monthlyIncome = $db->table('bookings')

            ->select("MONTH(created_at) as month, SUM(totalPrice) as income")
            ->where('status', 'CONFIRMED')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'total_income' => $totalIncome ?? 0,
            'total_visitors' => $totalVisitors ?? 0,
            'total_bookings_confirmed' => $totalBookingsConfirmed ?? 0,
            'total_bookings_not_pending' => $totalBookingsNotPending ?? 0,
            'total_active_tours' => $totalActiveTours ?? 0,
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
            ->limit(10)
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