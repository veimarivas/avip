<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsolidadoExportNuevo implements WithMultipleSheets
{
    protected $pedidosAgrupados;
    protected $productos;
    protected $fecha;
    protected $localidadId;
    protected $tipoPedido;

    public function __construct($pedidosAgrupados, $productos, $fecha, $localidadId, $tipoPedido)
    {
        $this->pedidosAgrupados = $pedidosAgrupados;
        $this->productos = $productos;
        $this->fecha = $fecha;
        $this->localidadId = $localidadId;
        $this->tipoPedido = $tipoPedido;
    }

    public function sheets(): array
    {
        $sheets = [];

        $tipos = [
            0 => 'Regulares',
            1 => 'Frial',
            2 => 'Frial Sofia'
        ];

        $tipoNombre = $tipos[$this->tipoPedido] ?? 'Todos';

        foreach ($this->pedidosAgrupados as $localidadId => $pedidosLocalidad) {
            $localidad = $pedidosLocalidad->first()->localidad;
            $sheetName = substr($localidad->nombre . ' ' . $tipoNombre, 0, 31);
            $sheets[] = new ConsolidadoSheetNuevo($pedidosLocalidad, $this->productos, $this->fecha, $sheetName);
        }

        return $sheets;
    }
}

class ConsolidadoSheetNuevo implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $pedidosLocalidad;
    protected $productos;
    protected $fecha;
    protected $title;

    public function __construct($pedidosLocalidad, $productos, $fecha, $title)
    {
        $this->pedidosLocalidad = $pedidosLocalidad;
        $this->productos = $productos;
        $this->fecha = $fecha;
        $this->title = $title;
    }

    public function collection()
    {
        $data = [];
        $totalesProductos = array_fill(0, count($this->productos), 0);
        $totalSubtotal = 0;

        foreach ($this->pedidosLocalidad as $pedido) {
            $fila = [
                'Localidad' => $pedido->localidad->nombre,
                'Cliente' => $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos,
                'Orden' => $pedido->cliente->orden,
            ];

            $subtotalCliente = 0;

            // Inicializar productos en 0
            foreach ($this->productos as $index => $producto) {
                $fila[$producto->nombre . ' (kg)'] = 0;
            }

            // Sumar pesos reales de productos procesados
            foreach ($pedido->pedidos_productos as $pedidoProducto) {
                if ($pedidoProducto->procesado == '1') {
                    $productoId = $pedidoProducto->producto_id;
                    $producto = $this->productos->firstWhere('id', $productoId);

                    if ($producto) {
                        $nombreColumna = $producto->nombre . ' (kg)';
                        $fila[$nombreColumna] += $pedidoProducto->peso_real;
                        $subtotalCliente += $pedidoProducto->costo_total;

                        // Acumular en totales
                        $indexProducto = $this->productos->search(function ($item) use ($productoId) {
                            return $item->id == $productoId;
                        });

                        if ($indexProducto !== false) {
                            $totalesProductos[$indexProducto] += $pedidoProducto->peso_real;
                        }
                    }
                }
            }

            $fila['Subtotal (Bs.)'] = $subtotalCliente;
            $totalSubtotal += $subtotalCliente;
            $data[] = $fila;
        }

        // Agregar fila de totales
        $filaTotales = [
            'Localidad' => 'TOTALES',
            'Cliente' => '',
            'Orden' => '',
        ];

        foreach ($this->productos as $index => $producto) {
            $filaTotales[$producto->nombre . ' (kg)'] = $totalesProductos[$index];
        }

        $filaTotales['Subtotal (Bs.)'] = $totalSubtotal;
        $data[] = $filaTotales;

        return collect($data);
    }

    public function headings(): array
    {
        $encabezados = ['Localidad', 'Cliente', 'Orden'];

        foreach ($this->productos as $producto) {
            $encabezados[] = $producto->nombre . ' (kg)';
        }

        $encabezados[] = 'Subtotal (Bs.)';

        return $encabezados;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para la fila de totales (última fila)
        $lastRow = count($this->pedidosLocalidad) + 2; // +2 por los encabezados y base 1
        $sheet->getStyle("A{$lastRow}:{$sheet->getHighestColumn()}{$lastRow}")
            ->getFont()
            ->setBold(true);

        return [
            1 => ['font' => ['bold' => true]], // Encabezados en negrita
        ];
    }
}
