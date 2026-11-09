<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsolidadoExportTodas implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $datosReporte;
    protected $productos;
    protected $fecha;
    protected $tipoPedido;
    protected $totalPedidos;
    protected $totalesPesos;
    protected $cantidadCajas;

    public function __construct($datosReporte, $productos, $fecha, $tipoPedido, $totalPedidos, $totalesPesos, $cantidadCajas)
    {
        $this->datosReporte = $datosReporte;
        $this->productos = $productos;
        $this->fecha = $fecha;
        $this->tipoPedido = $tipoPedido;
        $this->totalPedidos = $totalPedidos;
        $this->totalesPesos = $totalesPesos;
        $this->cantidadCajas = $cantidadCajas;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->datosReporte as $fila) {
            $rowData = [
                $fila['numero'],
                $fila['cliente_nombre'],
                $fila['cliente_ci'],
                $fila['localidad'],
            ];

            foreach ($this->productos as $productoIndex => $producto) {
                $peso = $fila["peso_{$producto->id}"] ?? 0;
                $rowData[] = $peso > 0 ? number_format($peso, 2) : '-';
            }

            $data[] = $rowData;
        }

        // Agregar fila de TOTALES (suma de pesos)
        $totalRow = [
            '',
            '',
            '',
            'TOTALES',
        ];

        foreach ($this->productos as $index => $producto) {
            $totalRow[] = $this->totalesPesos[$index] > 0 ? number_format($this->totalesPesos[$index], 2) : '-';
        }

        $data[] = $totalRow;

        // Agregar fila de CANTIDAD DE CAJAS (total dividido por n_caja y redondeado)
        $cajasRow = [
            '',
            '',
            '',
            'CANTIDAD DE CAJAS',
        ];

        foreach ($this->productos as $index => $producto) {
            $cajasRow[] = $this->cantidadCajas[$index] > 0 ? $this->cantidadCajas[$index] : '-';
        }

        $data[] = $cajasRow;

        return collect($data);
    }

    public function headings(): array
    {
        $headings = ['#', 'Cliente', 'CI', 'Localidad'];

        foreach ($this->productos as $producto) {
            $headings[] = $producto->nombre;
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->datosReporte) + 3; // +3 para encabezados + 2 filas de totales

        // Estilo para encabezados
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '405189']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DEE2E6']]
            ]
        ]);

        // Estilo para fila de TOTALES (penúltima fila)
        $totalRow = $lastRow - 1;
        $sheet->getStyle('A' . $totalRow . ':' . $sheet->getHighestColumn() . $totalRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9ECEF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        // Estilo para fila de CANTIDAD DE CAJAS (última fila)
        $cajasRow = $lastRow;
        $sheet->getStyle('A' . $cajasRow . ':' . $sheet->getHighestColumn() . $cajasRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '198754']], // Verde
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'DEE2E6']]
            ]
        ]);

        // Autoajustar columnas
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Centrar contenido
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $lastRow)
            ->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');

        // Alinear a la izquierda las columnas de cliente y localidad
        $sheet->getStyle('B2:B' . $lastRow)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D2:D' . $lastRow)->getAlignment()->setHorizontal('left');

        // Estilo para celdas con valores (solo en las filas de datos)
        $lastDataRow = count($this->datosReporte) + 1;
        for ($col = 'E'; $col <= $sheet->getHighestColumn(); $col++) {
            for ($row = 2; $row <= $lastDataRow; $row++) {
                $cellValue = $sheet->getCell($col . $row)->getValue();
                if ($cellValue !== '-' && $cellValue !== null) {
                    $sheet->getStyle($col . $row)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '0C5460']],
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1ECF1']],
                    ]);
                }
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // #
            'B' => 30,  // Cliente
            'C' => 15,  // CI
            'D' => 20,  // Localidad
        ];

        // Para cada producto, asignar ancho
        $columnIndex = 'E';
        foreach ($this->productos as $producto) {
            $widths[$columnIndex++] = 12; // Ancho para cada producto
        }

        return $widths;
    }

    public function title(): string
    {
        $titulos = [0 => 'Regulares', 1 => 'Frial', 2 => 'Frial Sofia'];
        $titulo = $titulos[$this->tipoPedido] ?? 'Desconocido';

        return "Reporte {$titulo}";
    }
}
