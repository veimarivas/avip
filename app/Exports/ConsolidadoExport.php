<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ConsolidadoExport implements WithMultipleSheets
{
    protected $pedidosAgrupados;
    protected $productosConsolidados;
    protected $fecha;

    public function __construct($pedidosAgrupados, $productosConsolidados, $fecha)
    {
        $this->pedidosAgrupados = $pedidosAgrupados;
        $this->productosConsolidados = $productosConsolidados;
        $this->fecha = $fecha;
    }

    public function sheets(): array
    {
        $sheets = [];

        $tipos = [
            0 => 'Regulares',
            1 => 'Frial',
            2 => 'Frial Sofia'
        ];

        foreach ($this->pedidosAgrupados as $localidadId => $tiposPedidos) {
            foreach ($tiposPedidos as $tipoPedido => $pedidos) {
                $localidad = $pedidos->first()->localidad;
                $sheetName = substr($localidad->nombre . ' ' . $tipos[$tipoPedido], 0, 31); // Excel limita a 31 caracteres
                $sheets[] = new ConsolidadoSheet($pedidos, $this->productosConsolidados, $this->fecha, $sheetName);
            }
        }

        return $sheets;
    }
}

class ConsolidadoSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $pedidos;
    protected $productosConsolidados;
    protected $fecha;
    protected $title;

    public function __construct($pedidos, $productosConsolidados, $fecha, $title)
    {
        $this->pedidos = $pedidos;
        $this->productosConsolidados = $productosConsolidados;
        $this->fecha = $fecha;
        $this->title = $title;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->pedidos as $pedido) {
            $fila = [
                'Localidad' => $pedido->localidad->nombre,
                'Cliente' => $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos,
                'CI' => $pedido->cliente->ci,
            ];

            // Inicializar pesos en 0 para cada producto
            foreach ($this->productosConsolidados as $producto) {
                $fila[$producto] = 0;
            }

            // Sumar los pesos reales de los productos procesados
            foreach ($pedido->pedidos_productos as $pedidoProducto) {
                $productoNombre = $pedidoProducto->producto->nombre;
                if (in_array($pedidoProducto->producto_id, [1, 2, 3])) {
                    $productoNombre = 'Pollo Primera';
                }

                $fila[$productoNombre] += $pedidoProducto->peso_real;
            }

            $data[] = $fila;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $encabezados = ['Localidad', 'Cliente', 'CI'];
        return array_merge($encabezados, $this->productosConsolidados);
    }

    public function title(): string
    {
        return $this->title;
    }
}
