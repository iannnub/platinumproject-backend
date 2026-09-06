<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;

class ExportController extends Controller
{
    public function excel(Request $request)
    {
        $filters = $request->only(['status', 'payment_status', 'date_from', 'date_to', 'search']);
        $filename = 'bookings_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new BookingsExport($filters), $filename);
    }
}
