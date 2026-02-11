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

class ConsumerMasterlistExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $status = null,
        protected ?int $blockId = null
    ) {}

    public function collection()
    {
        $query = Consumer::with(['user', 'block']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->blockId) {
            $query->where('block_id', $this->blockId);
        }

        return $query->orderBy('id_no')->get();
    }

    public function headings(): array
    {
        return [
            'ID No.',
            'Last Name',
            'First Name',
            'Middle Name',
            'Email',
            'Block',
            'Lot Number',
            'Status',
            'Registered Date',
        ];
    }

    public function map($consumer): array
    {
        return [
            $consumer->id_no,
            $consumer->user->last_name,
            $consumer->user->first_name,
            $consumer->user->middle_name ?? '',
            $consumer->user->email ?? '-',
            $consumer->block?->name ?? 'N/A',
            $consumer->lot_number,
            $consumer->status_label,
            $consumer->created_at->format('Y-m-d'),
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
        return 'Consumer Masterlist';
    }
}
