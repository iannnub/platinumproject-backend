<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentLog;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class FinancialController extends Controller
{
    /**
     * GET /api/financial/dashboard
     * Get financial overview for dashboard
     */
    public function dashboard(Request $request)
    {
        $period    = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $cacheKey = "fin_dashboard_{$period}_{$startDate}_{$endDate}";

        $data = Cache::remember($cacheKey, 60, function () use ($period, $startDate, $endDate) {
            $dateRange = $this->getDateRange($period, $startDate, $endDate);

            $bookings = Booking::with(['paymentLogs', 'expenses'])
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->get();

            $totalRevenue  = (float) $bookings->sum('total_paid');
            $totalExpenses = (float) $bookings->sum(fn($b) => $b->total_expenses);
            $netProfit     = $totalRevenue - $totalExpenses;

            $expectedRevenue = (float) $bookings->sum('total_amount');
            $expectedProfit  = $expectedRevenue - $totalExpenses;

            $averageProfitMargin = $expectedRevenue > 0
                ? round(($expectedProfit / $expectedRevenue) * 100, 2)
                : 0;

            $bookingStats = [
                'total'      => $bookings->count(),
                'pending'    => $bookings->where('payment_status', 'pending')->count(),
                'dp_paid'    => $bookings->where('payment_status', 'dp_paid')->count(),
                'fully_paid' => $bookings->whereIn('payment_status', ['fully_paid', 'paid'])->count(),
            ];

            return [
                'period' => [
                    'type'  => $period,
                    'start' => $dateRange['start']->format('Y-m-d'),
                    'end'   => $dateRange['end']->format('Y-m-d'),
                ],
                'summary' => [
                    'total_revenue'          => $totalRevenue,
                    'total_expenses'         => $totalExpenses,
                    'net_profit'             => $netProfit,
                    'expected_revenue'       => $expectedRevenue,
                    'expected_profit'        => $expectedProfit,
                    'average_profit_margin'  => $averageProfitMargin,
                    'outstanding_payments'   => (float) ($expectedRevenue - $totalRevenue),
                ],
                'booking_stats' => $bookingStats,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/financial/cash-flow
     * Get monthly cash flow data for charts (optimized single query with caching)
     */
    public function cashFlow(Request $request)
    {
        $year     = (int) $request->get('year', date('Y'));
        $cacheKey = "fin_cashflow_{$year}";

        $data = Cache::remember($cacheKey, 120, function () use ($year) {
            $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
            $endOfYear   = Carbon::create($year, 12, 31)->endOfYear();

            // Single query with eager loading instead of 12 separate queries
            $allBookings = Booking::with(['expenses', 'paymentLogs'])
                ->whereBetween('created_at', [$startOfYear, $endOfYear])
                ->get();

            $months = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
                $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth();

                $monthBookings = $allBookings->filter(function ($b) use ($monthStart, $monthEnd) {
                    return $b->created_at >= $monthStart && $b->created_at <= $monthEnd;
                });

                $revenue  = (float) $monthBookings->sum('total_paid');
                $expenses = (float) $monthBookings->sum(fn($b) => $b->total_expenses);
                $profit   = $revenue - $expenses;

                $months[] = [
                    'month'        => $monthStart->format('M'),
                    'month_number' => $month,
                    'revenue'      => $revenue,
                    'expenses'     => $expenses,
                    'profit'       => $profit,
                ];
            }

            return [
                'year'   => $year,
                'months' => $months,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/financial/profit-by-package
     * Get profit breakdown by package type
     */
    public function profitByPackage(Request $request)
    {
        $period    = $request->get('period', 'year');
        $dateRange = $this->getDateRange($period, null, null);

        $packages = DB::table('bookings')
            ->select(
                'package_type as name',
                DB::raw('COUNT(id) as booking_count'),
                DB::raw('SUM(total_paid) as total_revenue'),
                DB::raw('SUM(COALESCE(total_amount, 0)) as expected_revenue')
            )
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('package_type')
            ->get();

        $packagesWithProfit = $packages->map(function ($package) {
            $bookingIds    = Booking::where('package_type', $package->name)->pluck('id');
            $totalExpenses = (float) Expense::whereIn('booking_id', $bookingIds)->sum('amount');

            $package->total_expenses               = $totalExpenses;
            $package->net_profit                   = (float) ($package->total_revenue - $totalExpenses);
            $package->expected_profit              = (float) ($package->expected_revenue - $totalExpenses);
            $package->average_profit_per_booking   = $package->booking_count > 0
                ? round($package->expected_profit / $package->booking_count, 2)
                : 0;
            $package->profit_margin = $package->expected_revenue > 0
                ? round(($package->expected_profit / $package->expected_revenue) * 100, 2)
                : 0;

            return $package;
        });

        return response()->json([
            'success' => true,
            'data'    => $packagesWithProfit,
        ]);
    }

    /**
     * GET /api/financial/bookings
     * Get detailed financial info for all bookings
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['paymentLogs', 'expenses']);

        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->has('package_type')) {
            $query->where('package_type', $request->package_type);
        }
        if ($request->has('period') && $request->period !== 'all') {
            $dateRange = $this->getDateRange($request->period, $request->get('start_date'), $request->get('end_date'));
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        } elseif ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        $result = $bookings->map(function ($booking) {
            return [
                'id'             => $booking->id,
                'booking_code'   => $booking->booking_code,
                'customer'       => $booking->bride_names,
                'package'        => [
                    'name'  => $booking->package_type,
                    'price' => (float) $booking->total_amount,
                ],
                'event_date'     => $booking->event_date?->format('Y-m-d'),
                'payment_status' => $booking->payment_status,
                'financial'      => [
                    'dp_amount'       => (float) $booking->dp_amount,
                    'total_paid'      => (float) $booking->total_paid,
                    'remaining_amount'=> (float) $booking->remaining_amount,
                    'total_expenses'  => (float) $booking->total_expenses,
                    'cash_position'   => (float) $booking->cash_position,
                    'expected_profit' => (float) $booking->expected_profit,
                    'profit_margin'   => round($booking->profit_margin, 2),
                ],
                'expenses'       => $booking->expenses->map(fn($e) => [
                    'id'          => $e->id,
                    'item_name'   => $e->item_name,
                    'description' => $e->description,
                    'amount'      => (float) $e->amount,
                    'date'        => $e->expense_date ? $e->expense_date->format('Y-m-d') : null,
                ]),
                'created_at' => $booking->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * GET /api/financial/booking/{id}
     * Get detailed financial info for single booking
     */
    public function bookingDetail($id)
    {
        $booking = Booking::with(['paymentLogs', 'expenses.creator'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'booking' => [
                    'id'           => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'bride_names'  => $booking->bride_names,
                    'initials'     => $booking->initials,
                    'phone'        => $booking->phone,
                    'event_date'   => $booking->event_date?->format('Y-m-d'),
                    'event_type'   => $booking->event_type,
                    'package'      => [
                        'name'  => $booking->package_type,
                        'price' => (float) $booking->total_amount,
                    ],
                ],
                'payment' => [
                    'status'           => $booking->payment_status,
                    'dp_amount'        => (float) $booking->dp_amount,
                    'total_paid'       => (float) $booking->total_paid,
                    'remaining_amount' => (float) $booking->remaining_amount,
                    'payment_logs'     => $booking->paymentLogs->map(fn($log) => [
                        'id'     => $log->id,
                        'type'   => $log->payment_type,
                        'amount' => (float) $log->amount,
                        'date'   => $log->payment_date->format('Y-m-d'),
                        'method' => $log->payment_method,
                        'notes'  => $log->notes,
                    ]),
                ],
                'expenses' => [
                    'items' => $booking->expenses->map(fn($expense) => [
                        'id'          => $expense->id,
                        'item_name'   => $expense->item_name,
                        'description' => $expense->description,
                        'amount'      => (float) $expense->amount,
                        'date'        => $expense->expense_date->format('Y-m-d'),
                        'created_by'  => $expense->creator?->name ?? 'Admin',
                        'created_at'  => $expense->created_at->format('Y-m-d H:i:s'),
                    ]),
                    'total' => (float) $booking->total_expenses,
                ],
                'profit_analysis' => [
                    'cash_position'    => [
                        'value'       => (float) $booking->cash_position,
                        'description' => 'Total Paid - Total Expenses (Real cash flow)',
                    ],
                    'expected_profit'  => [
                        'value'       => (float) $booking->expected_profit,
                        'description' => 'Package Price - Total Expenses (Potential profit)',
                    ],
                    'profit_margin'        => round($booking->profit_margin, 2),
                    'outstanding_payment'  => (float) $booking->remaining_amount,
                ],
            ],
        ]);
    }

    /**
     * POST /api/financial/payment
     * Record a payment (DP or pelunasan)
     */
    public function recordPayment(Request $request)
    {
        $validated = $request->validate([
            'booking_id'     => 'required|exists:bookings,id',
            'payment_type'   => 'required|in:dp,pelunasan,lainnya',
            'amount'         => 'required|numeric|min:0',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:transfer,cash,ewallet',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $paymentLog = PaymentLog::create([
                'booking_id'     => $validated['booking_id'],
                'payment_type'   => $validated['payment_type'],
                'amount'         => $validated['amount'],
                'payment_date'   => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes'          => $validated['notes'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            $booking = Booking::findOrFail($validated['booking_id']);
            $booking->total_paid      = (float) $booking->total_paid + (float) $validated['amount'];
            $packagePrice             = (float) ($booking->total_amount ?? 0);
            $booking->remaining_amount = max(0, $packagePrice - $booking->total_paid);

            // Update payment status
            if ($packagePrice > 0 && $booking->total_paid >= $packagePrice) {
                $booking->payment_status = 'fully_paid';
            } elseif ((float) $booking->total_paid >= (float) $booking->dp_amount) {
                $booking->payment_status = 'dp_paid';
            }

            $booking->save();

            DB::commit();
            $this->clearFinancialCache();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data'    => [
                    'payment_log' => $paymentLog,
                    'booking'     => [
                        'payment_status'   => $booking->payment_status,
                        'total_paid'       => (float) $booking->total_paid,
                        'remaining_amount' => (float) $booking->remaining_amount,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/financial/expense
     * Add expense item to booking
     */
    public function addExpense(Request $request)
    {
        $validated = $request->validate([
            'booking_id'   => 'required|exists:bookings,id',
            'item_name'    => 'required|string|max:255',
            'description'  => 'nullable|string',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $expense = Expense::create([
            'booking_id'   => $validated['booking_id'],
            'item_name'    => $validated['item_name'],
            'description'  => $validated['description'] ?? null,
            'amount'       => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes'        => $validated['notes'] ?? null,
            'created_by'   => auth()->id(),
        ]);

        $this->clearFinancialCache();

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully',
            'data'    => $expense,
        ], 201);
    }

    /**
     * PUT /api/financial/expense/{id}
     * Update expense item
     */
    public function updateExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'item_name'    => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'amount'       => 'sometimes|numeric|min:0',
            'expense_date' => 'sometimes|date',
            'notes'        => 'nullable|string',
        ]);

        $expense->update($validated);
        $this->clearFinancialCache();

        return response()->json([
            'success' => true,
            'message' => 'Expense updated successfully',
            'data'    => $expense,
        ]);
    }

    /**
     * DELETE /api/financial/expense/{id}
     * Delete expense item
     */
    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        $this->clearFinancialCache();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully',
        ]);
    }

    /**
     * Invalidate financial dashboard cache on data changes
     */
    private function clearFinancialCache(): void
    {
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            // Silently continue if cache flush fails
        }
    }

    /**
     * Helper: Get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null): array
    {
        $now = Carbon::now();

        return match ($period) {
            'day'    => ['start' => $now->copy()->startOfDay(),   'end' => $now->copy()->endOfDay()],
            'week'   => [
                'start' => $now->copy()->startOfWeek(Carbon::SUNDAY),
                'end'   => $now->copy()->endOfWeek(Carbon::SATURDAY),
            ],
            'month'  => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            'year'   => ['start' => $now->copy()->startOfYear(),  'end' => $now->copy()->endOfYear()],
            'custom' => [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end'   => Carbon::parse($endDate)->endOfDay(),
            ],
            default  => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
        };
    }
}
