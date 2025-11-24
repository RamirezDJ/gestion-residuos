<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistroSemanalExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $tituloFecha;
    protected $registros;

    public function __construct($registros, $tituloFecha)
    {
        $this->tituloFecha = $tituloFecha;
        $this->registros = $registros;
    }

    public function collection()
    {
        return $this->registros->map(function ($registro) {
            return [
                'fecha' => Carbon::parse($registro->fecha)->format('d/m/Y'),
                'turno' => $registro->turno,
                'zona' => $registro->zona_nombre,
                'areaAsignada' => $registro->area_nombre,
                'subproducto' => $registro->subproducto_nombre,

                // ▼▼ CORRECCIÓN: Forzamos formato de número para que el 0.00 sea visible ▼▼
                'kilos' => number_format((float)$registro->kilos, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Turno',
            'Zona',
            'Área Asignada',
            'Categoría',
            'Kilos (kg)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2 => ['font' => ['bold' => true]],
            'A2:F5000' => ['alignment' => ['horizontal' => 'center']],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 30,
            'D' => 35,
            'E' => 30,
            'F' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'Datos Generados en la Semana ' . $this->tituloFecha);
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '0a0a0a']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getRowDimension('1')->setRowHeight(30);

                $headings = $this->headings();
                foreach ($headings as $index => $heading) {
                    $sheet->setCellValueByColumnAndRow($index + 1, 2, $heading);
                }
                $sheet->getStyle('A2:F2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                $sheet->setAutoFilter('A2:F2');
            },
        ];
    }
}
