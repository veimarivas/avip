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

    protected array $filas = [];
    protected array $filasGrupo = [];
    protected array $filasSubtotal = [];
    protected int $ultimaFila = 5;
    protected bool $filasConstruidas = false;

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

    /**
     * Construye las filas del reporte:
     * - Encabezado de cada usuario.
     * - Detalle de cada cobro con su cliente.
     * - Subtotal por usuario.
     * - Total general al final.
     */
    private function construirFilas(): void
    {
        if ($this->filasConstruidas) {
            return;
        }

        $this->filasConstruidas = true;
        $fila = 5;

        foreach ($this->cobrosPorUsuario as $cobro) {
            $nombreUsuario = trim((string) ($cobro['nombre_completo'] ?? '')) ?: 'Sin usuario';

            $this->filas[] = [
                'USUARIO: ' . $nombreUsuario,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            $this->filasGrupo[] = $fila++;

            foreach (($cobro['detalles'] ?? []) as $detalle) {
                $etiqueta = '   Cliente: ' . (trim((string) ($detalle['cliente'] ?? '')) ?: 'Sin cliente');

                if (!empty($detalle['recibo'])) {
                    $etiqueta .= ' · Recibo ' . $detalle['recibo'];
                }

                if (!empty($detalle['hora'])) {
                    $etiqueta .= ' · ' . $detalle['hora'];
                }

                $this->filas[] = [
                    $etiqueta,
                    round((float) ($detalle['efectivo'] ?? 0), 2),
                    round((float) ($detalle['qr'] ?? 0), 2),
                    round((float) ($detalle['parcial_efectivo'] ?? 0), 2),
                    round((float) ($detalle['parcial_qr'] ?? 0), 2),
                    round((float) ($detalle['descuento'] ?? 0), 2),
                    round((float) ($detalle['total'] ?? 0), 2),
                    1,
                ];
                $fila++;
            }

            $this->filas[] = [
                'Subtotal ' . $nombreUsuario,
                round((float) ($cobro['efectivo'] ?? 0), 2),
                round((float) ($cobro['qr'] ?? 0), 2),
                round((float) ($cobro['parcial_efectivo'] ?? 0), 2),
                round((float) ($cobro['parcial_qr'] ?? 0), 2),
                round((float) ($cobro['descuento'] ?? 0), 2),
                round((float) ($cobro['total'] ?? 0), 2),
                (int) ($cobro['cantidad_recibos'] ?? 0),
            ];
            $this->filasSubtotal[] = $fila++;
        }

        $this->filas[] = [
            'TOTALES',
            round((float) ($this->totalesGenerales['efectivo'] ?? 0), 2),
            round((float) ($this->totalesGenerales['qr'] ?? 0), 2),
            round((float) ($this->totalesGenerales['parcial_efectivo'] ?? 0), 2),
            round((float) ($this->totalesGenerales['parcial_qr'] ?? 0), 2),
            round((float) ($this->totalesGenerales['descuento'] ?? 0), 2),
            round((float) ($this->totalesGenerales['total'] ?? 0), 2),
            array_sum(array_column($this->cobrosPorUsuario, 'cantidad_recibos')),
        ];
        $this->ultimaFila = $fila;
    }

    public function collection()
    {
        $this->construirFilas();

        return collect($this->filas);
    }

    public function headings(): array
    {
        return [
            'Usuario / Cliente',
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
        $this->construirFilas();

        $styles = [
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
        ];

        foreach ($this->filasGrupo as $fila) {
            $styles[$fila] = [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF14503C'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFEAF3EC'],
                ],
            ];
        }

        foreach ($this->filasSubtotal as $fila) {
            $styles[$fila] = [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF0F241C'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFF7FAF6'],
                ],
            ];
        }

        $styles[$this->ultimaFila] = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF0F241C'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['argb' => 'FFFBF0D5'],
            ],
        ];

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 52,
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
                $this->construirFilas();

                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->ultimaFila;
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
