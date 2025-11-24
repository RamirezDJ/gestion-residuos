<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell; // <--- 1. IMPORTANTE
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// 2. Agregamos la interfaz WithCustomStartCell
class RegistroSubproductosExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $datosGenerados;
    protected $inicio;
    protected $final;

    public function __construct($datosGenerados, $inicio, $final)
    {
        $this->datosGenerados = $datosGenerados;
        $this->inicio = $inicio;
        $this->final = $final;
    }

    // 3. Definimos que los datos (y encabezados automáticos) inicien en la fila 2
    public function startCell(): string
    {
        return 'A2';
    }

    public function collection()
    {
        return $this->datosGenerados->map(function ($registro) {
            return [
                'fecha' => Carbon::parse($registro->fecha)->format('d/m/Y'),
                'zona' => $registro->zona_nombre,
                'subproducto' => $registro->subproducto_nombre,
                'total_kg' => number_format((float)$registro->total_kg, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Zona',
            'Subproducto',
            'Total (kg)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2 => ['font' => ['bold' => true]],
            'A2:D5000' => ['alignment' => ['horizontal' => 'center']],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 30,
            'D' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $inicioFmt = Carbon::parse($this->inicio)->format('d/m/Y');
                $finalFmt = Carbon::parse($this->final)->format('d/m/Y');

                // Título en A1 (Ahora la fila 1 está vacía gracias a startCell)
                $sheet->setCellValue('A1', 'Reporte de Subproductos del ' . $inicioFmt . ' al ' . $finalFmt);

                $sheet->mergeCells('A1:D1');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '0a0a0a']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getRowDimension('1')->setRowHeight(30);

                // Estilo de encabezados (que ya están en la fila 2 automáticamente)
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                $sheet->setAutoFilter('A2:D2');
            },
        ];
    }
}
