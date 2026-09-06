<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        $query = Booking::query()->with('user')->orderByDesc('created_at');

        if ($search = $this->filters['search'] ?? null) {
            $query->where(fn ($q) => $q
                ->where('bride_names', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('booking_code', 'like', "%{$search}%")
            );
        }
        if ($status = $this->filters['status'] ?? null) {
            $query->where('status', $status);
        }
        if ($paymentStatus = $this->filters['payment_status'] ?? null) {
            $query->where('payment_status', $paymentStatus);
        }
        if ($dateFrom = $this->filters['date_from'] ?? null) {
            $query->where('event_date', '>=', $dateFrom);
        }
        if ($dateTo = $this->filters['date_to'] ?? null) {
            $query->where('event_date', '<=', $dateTo);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Booking',
            'Nama Mempelai',
            'Initial',
            'Tanggal Acara',
            'Jenis Acara',
            'Dekor Akad',
            'No WhatsApp',
            'Alamat',
            'Link Maps',
            'Paket',
            'DP (Rp)',
            'Total (Rp)',
            'Status Pembayaran',
            'Status Booking',
            'Catatan',
            'Tgl Booking',
        ];
    }

    public function map($booking): array
    {
        static $no = 0;
        $no++;

        $paymentLabels = [
            'pending' => 'Belum Bayar',
            'dp_paid' => 'DP Terbayar',
            'paid'    => 'Lunas',
        ];
        $statusLabels = [
            'pending'   => 'Pending',
            'confirmed' => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        return [
            $no,
            $booking->booking_code,
            $booking->bride_names,
            $booking->initials,
            \Carbon\Carbon::parse($booking->event_date)->format('d/m/Y'),
            $booking->event_type,
            $booking->decoration_type,
            $booking->phone,
            $booking->address,
            $booking->maps_url,
            $booking->package_type,
            number_format($booking->dp_amount, 0, ',', '.'),
            $booking->total_amount ? number_format($booking->total_amount, 0, ',', '.') : '-',
            $paymentLabels[$booking->payment_status] ?? $booking->payment_status,
            $statusLabels[$booking->status] ?? $booking->status,
            $booking->notes ?? '-',
            \Carbon\Carbon::parse($booking->created_at)->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2A2A2A']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
