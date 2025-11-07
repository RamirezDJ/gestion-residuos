<?php

namespace App; // Asegúrate que tu namespace sea 'App\Exports' si así lo tienes
namespace App\Exports; // <-- O este

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
    protected $tituloFecha; // Cambiado de $fecha a $tituloFecha
    protected $registros;

    // El constructor ahora recibe la colección y el título
    public function __construct($registros, $tituloFecha)
    {
        $this->tituloFecha = $tituloFecha;
        $this->registros = $registros;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Mapeamos la colección para formatear la fecha y seleccionar columnas
        return $this->registros->map(function ($registro) {
            return [
                'fecha' => Carbon::parse($registro->fecha)->format('d/m/Y'), // Formateamos fecha
                'turno' => $registro->turno,
                'zona' => $registro->zona_nombre,   // Usamos los nombres ya seleccionados
                'areaAsignada' => $registro->area_nombre,
                'subproducto' => $registro->subproducto_nombre, // Nueva columna
                'kilos' => $registro->kilos,         // Columna renombrada
            ];
        });
    }

    // Encabezados actualizados
    public function headings(): array
    {
        return [
            'Fecha',
            'Turno',
            'Zona',
            'Área Asignada',
            'Subproducto', // Nuevo encabezado
            'Kilos (kg)',  // Encabezado renombrado
        ];
    }

    // Estilos (Ajustamos rango)
    public function styles(Worksheet $sheet)
    {
        return [
            // Fila 2 (encabezados) en negrita
            2    => ['font' => ['bold' => true]],
            // Rango ajustado para 6 columnas (A hasta F)
            'A2:F1000' => ['alignment' => ['horizontal' => 'center']], // Aumentado el rango de filas
        ];
    }

    // Anchos de columna (Ajustamos y añadimos F)
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Fecha
            'B' => 15, // Turno
            'C' => 30, // Zona
            'D' => 35, // Área Asignada
            'E' => 30, // Subproducto (Nuevo)
            'F' => 15, // Kilos (Antes G)
        ];
    }

    // Eventos (Ajustamos título, rangos y columnas)
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Título en la fila 1 (Usa la variable $tituloFecha)
                $sheet->setCellValue('A1', 'Datos Generados en la Semana ' . $this->tituloFecha);
                $sheet->mergeCells('A1:F1'); // Rango ajustado a 6 columnas
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '0a0a0a']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getRowDimension('1')->setRowHeight(30);

                // Encabezados en la fila 2
                $headings = $this->headings();
                foreach ($headings as $index => $heading) {
                    $sheet->setCellValueByColumnAndRow($index + 1, 2, $heading);
                }
                $sheet->getStyle('A2:F2')->applyFromArray([ // Rango ajustado
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // Filtro automático
                $sheet->setAutoFilter('A2:F2'); // Rango ajustado
            },
        ];
    }
}
