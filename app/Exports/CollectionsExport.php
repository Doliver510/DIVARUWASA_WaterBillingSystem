<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollectionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected string $startDate,
        protected string $endDate
    ) {}

    public function collection()
    {
        return Payment::with(['consumer.user', 'processedBy', 'bill'])
            ->whereBetween('paid_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59'])
            ->orderBy('paid_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Receipt No.',
            'Date',
            'Time',
            'Consumer ID',
            'Consumer Name',
            'Bill Period',
            'Amount',
            'Processed By',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->receipt_number,
            $payment->paid_at->format('Y-m-d'),
            $payment->paid_at->format('H:i:s'),
            $payment->consumer->id_no,
            $payment->consumer->full_name,
            $payment->bill->billing_period,
            $payment->amount,
            $payment->processedBy->full_name,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Collections Report';
    }
}
