<?php

namespace App\Exports;

use App\Models\Bill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BillingSummaryExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected string $period
    ) {}

    public function collection()
    {
        return Bill::with(['consumer.user'])
            ->where('billing_period', $this->period)
            ->orderBy('consumer_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Consumer ID',
            'Consumer Name',
            'Address',
            'Consumption (cu.m)',
            'Water Charge',
            'Arrears',
            'Penalty',
            'Other Charges',
            'Total Amount',
            'Amount Paid',
            'Balance',
            'Status',
        ];
    }

    public function map($bill): array
    {
        return [
            $bill->consumer->id_no,
            $bill->consumer->full_name,
            $bill->consumer->address,
            $bill->consumption,
            $bill->water_charge,
            $bill->arrears,
            $bill->penalty,
            $bill->other_charges,
            $bill->total_amount,
            $bill->amount_paid,
            $bill->balance,
            $bill->status_label,
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
        return 'Billing Summary - '.$this->period;
    }
}
