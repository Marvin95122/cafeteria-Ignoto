<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportArraySheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    private string $title;
    private array $headings;
    private array $rows;

    public function __construct(string $title, array $headings, array $rows)
    {
        $this->title = mb_substr($title, 0, 31);
        $this->headings = $headings;
        $this->rows = $rows;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '78350F'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->setAutoFilter("A1:{$highestColumn}{$highestRow}");
                $sheet->freezePane('A2');

                $sheet->getStyle("A1:{$highestColumn}1")->getAlignment()->setHorizontal('center');

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin')
                    ->getColor()
                    ->setRGB('E5E7EB');

                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType('solid')
                            ->getStartColor()
                            ->setRGB('FFF7ED');
                    }
                }
            },
        ];
    }
}