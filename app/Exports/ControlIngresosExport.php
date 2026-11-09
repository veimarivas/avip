<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ControlIngresosExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $reporte;
    protected $fechaInicio;
    protected $fechaFin;
    protected $totalesGenerales;

    public function __construct($reporte, $fechaInicio, $fechaFin, $totalesGenerales)
    {
        $this->reporte = $reporte;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->totalesGenerales = $totalesGenerales;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->reporte as $index => $item) {
            $data[] = [
                '#' => $index + 1,
                'Usuario' => $item['usuario']['name'],
                'Email' => $item['usuario']['email'],
                'Total Efectivo (Bs.)' => $item['total_efectivo'],
                'Total QR (Bs.)' => $item['total_qr'],
                'Total General (Bs.)' => $item['total_general'],
                'Pagos Realizados' => $item['cantidad_pagos']
            ];
        }

        // Agregar fila de totales
        $data[] = [
            '#' => '',
            'Usuario' => 'TOTALES GENERALES',
            'Email' => '',
            'Total Efectivo (Bs.)' => $this->totalesGenerales['total_efectivo'],
            'Total QR (Bs.)' => $this->totalesGenerales['total_qr'],
            'Total General (Bs.)' => $this->totalesGenerales['total_general'],
            'Pagos Realizados' => $this->totalesGenerales['total_pagos']
        ];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            '#',
            'Usuario',
            'Email',
            'Total Efectivo (Bs.)',
            'Total QR (Bs.)',
            'Total General (Bs.)',
            'Pagos Realizados'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3498DB']]
        ]);

        $lastRow = count($this->reporte) + 2;
        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F1C40F']]
        ]);

        // Ajustar columnas automáticamente
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 30,
            'C' => 35,
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 20
        ];
    }
}
