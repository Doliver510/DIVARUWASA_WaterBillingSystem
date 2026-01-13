<?php

namespace App\Exports;

use App\Models\MeterReading;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsumptionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int $year
    ) {}

    public function collection()
    {
        return MeterReading::selectRaw('billing_period, SUM(consumption) as total_consumption, COUNT(*) as reading_count, AVG(consumption) as avg_consumption')
            ->whereRaw('billing_period LIKE ?', [$this->year.'-%'])
            ->groupBy('billing_period')
            ->orderBy('billing_period')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Billing Period',
            'Total Consumption (cu.m)',
            'Number of Readings',
            'Average Consumption (cu.m)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->billing_period,
            number_format($row->total_consumption, 2),
            $row->reading_count,
            number_format($row->avg_consumption, 2),
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
        return 'Consumption Report - '.$this->year;
    }
}
