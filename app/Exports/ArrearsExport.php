<?php

namespace App\Exports;

use App\Models\Consumer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArrearsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $blockId = null
    ) {}

    public function collection()
    {
        $query = Consumer::with(['user', 'block', 'bills' => function ($q) {
            $q->where('balance', '>', 0);
        }])
            ->whereHas('bills', function ($q) {
                $q->where('balance', '>', 0);
            });

        if ($this->blockId) {
            $query->where('block_id', $this->blockId);
        }

        return $query->get()->map(function ($consumer) {
            $consumer->total_arrears = $consumer->bills->sum('balance');
            $consumer->unpaid_bills_count = $consumer->bills->count();

            return $consumer;
        })->sortByDesc('total_arrears');
    }

    public function headings(): array
    {
        return [
            'Consumer ID',
            'Consumer Name',
            'Address',
            'Status',
            'Unpaid Bills',
            'Total Arrears',
        ];
    }

    public function map($consumer): array
    {
        return [
            $consumer->id_no,
            $consumer->full_name,
            $consumer->address,
            $consumer->status,
            $consumer->unpaid_bills_count,
            $consumer->total_arrears,
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
        return 'Arrears Report';
    }
}
