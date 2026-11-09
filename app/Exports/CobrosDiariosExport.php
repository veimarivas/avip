<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CobrosDiariosExport implements FromCollection, WithHeadings, WithCustomStartCell, WithStyles, WithColumnWidths, WithEvents
{
    protected array $cobrosPorUsuario;
    protected array $totalesGenerales;
    protected string $fecha;
    protected string $usuarioFiltro;
    protected string $tipoFiltro;

    public function __construct(
        array $cobrosPorUsuario,
        array $totalesGenerales,
        string $fecha,
        string $usuarioFiltro,
        string $tipoFiltro
    ) {
        $this->cobrosPorUsuario = $cobrosPorUsuario;
        $this->totalesGenerales = $totalesGenerales;
        $this->fecha = $fecha;
        $this->usuarioFiltro = $usuarioFiltro;
        $this->tipoFiltro = $tipoFiltro;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->cobrosPorUsuario as $cobro) {
            $rows[] = [
                $cobro['nombre_completo'] ?? 'Sin usuario',
                round((float) ($cobro['efectivo'] ?? 0), 2),
                round((float) ($cobro['qr'] ?? 0), 2),
                round((float) ($cobro['parcial_efectivo'] ?? 0), 2),
                round((float) ($cobro['parcial_qr'] ?? 0), 2),
                round((float) ($cobro['descuento'] ?? 0), 2),
                round((float) ($cobro['total'] ?? 0), 2),
                (int) ($cobro['cantidad_recibos'] ?? 0),
            ];
        }

        $rows[] = [
            'TOTALES',
            round((float) ($this->totalesGenerales['efectivo'] ?? 0), 2),
            round((float) ($this->totalesGenerales['qr'] ?? 0), 2),
            round((float) ($this->totalesGenerales['parcial_efectivo'] ?? 0), 2),
            round((float) ($this->totalesGenerales['parcial_qr'] ?? 0), 2),
            round((float) ($this->totalesGenerales['descuento'] ?? 0), 2),
            round((float) ($this->totalesGenerales['total'] ?? 0), 2),
            array_sum(array_column($this->cobrosPorUsuario, 'cantidad_recibos')),
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Usuario',
            'Efectivo (Bs.)',
            'QR (Bs.)',
            'Efectivo parcial (Bs.)',
            'QR parcial (Bs.)',
            'Descuento (Bs.)',
            'Total (Bs.)',
            'Recibos',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 5 + count($this->cobrosPorUsuario);

        return [
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FF14503C'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            $lastRow => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF0F241C'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFFBF0D5'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 18,
            'C' => 18,
            'D' => 22,
            'E' => 20,
            'F' => 20,
            'G' => 18,
            'H' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 5 + count($this->cobrosPorUsuario);
                $fechaMostrar = $this->fecha;

                if ($this->fecha && strtotime($this->fecha)) {
                    $fechaMostrar = date('d/m/Y', strtotime($this->fecha));
                }

                $sheet->setCellValue('A1', 'REPORTE DE COBROS DIARIOS');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 15,
                        'color' => ['argb' => 'FF14503C'],
                    ],
                ]);

                $sheet->setCellValue(
                    'A2',
                    'Fecha: ' . $fechaMostrar .
                    '    |    Usuario: ' . $this->usuarioFiltro .
                    '    |    Tipo de pago: ' . $this->tipoFiltro
                );
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'italic' => true,
                        'color' => ['argb' => 'FF67796D'],
                    ],
                ]);

                $sheet->getStyle('A4:H' . $lastRow)->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()
                    ->setARGB('FFE2E9DF');

                $sheet->getStyle('B5:G' . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('B5:H' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle('A4:H4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->freezePane('A5');
            },
        ];
    }
}
