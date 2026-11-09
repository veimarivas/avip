<?php

namespace App\Exports;

use App\Models\Pago;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecibosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $pagos;

    public function __construct($pagos)
    {
        $this->pagos = $pagos;
    }

    public function collection()
    {
        return $this->pagos;
    }

    public function headings(): array
    {
        return [
            'Recibo',
            'Fecha',
            'Cliente',
            'CI',
            'Localidad',
            'Monto Pagado (Bs)',
            'Descuento (Bs)',
            'Total (Bs)',
            'Tipo de Pago',
            'Usuario',
            'Observaciones'
        ];
    }

    public function map($pago): array
    {
        $clienteNombre = $pago->cliente
            ? trim(($pago->cliente->nombres ?? '') . ' ' . ($pago->cliente->apellidos ?? ''))
            : 'N/A';

        $clienteCi = $pago->cliente->ci ?? 'N/A';
        $localidad = $pago->cliente && $pago->cliente->localidad
            ? $pago->cliente->localidad->nombre
            : 'N/A';

        return [
            $pago->recibo,
            $pago->created_at ? $pago->created_at->format('d/m/Y H:i:s') : 'N/A',
            $clienteNombre !== '' ? $clienteNombre : 'N/A',
            $clienteCi,
            $localidad,
            $pago->pago_bs,
            $pago->descuento_bs,
            $pago->pago_bs + $pago->descuento_bs,
            strtoupper($pago->tipo),
            $pago->usuario->name ?? 'Sistema',
            $pago->observaciones ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la cabecera
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '405189']]
            ],
        ];
    }
}
