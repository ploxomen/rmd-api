<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderProductionExport implements
    FromView,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    protected $order;

    protected $details;

    protected $userName;

    public function __construct($order, $details, $userName)
    {
        $this->order = $order;
        $this->details = $details;
        $this->userName = $userName;
    }

    /**
     * Blade que será convertido a Excel.
     */
    public function view(): View
    {
        $ordersDetails = $this->order->numberOrders();

        $total = 0;

        foreach ($this->details as $detail) {
            $total += $detail->subtotal;
        }

        return view('reports.order-production-excel', [
            'order' => $this->order,
            'details' => $this->details,
            'ordersDetails' => $ordersDetails,
            'total' => $total,
            'userName' => $this->userName,
        ]);
    }

    /**
     * Ancho de columnas.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 40,
            'C' => 12,
            'D' => 23,
            'E' => 25,
            'F' => 20,
            'G' => 20,
            'H' => 18,
        ];
    }
    // public function drawings()
    // {
    //     $drawings = [];
    //     $currentRow = 12; // Fila inicial del detalle (A12)

    //     foreach ($this->details as $detail) {
    //         $pathImg = $detail->product_img;
    //         $rowSpan = max(1, $detail->list_labels->count());

    //         // Validar que exista la ruta y el archivo físico
    //         if (!empty($pathImg) && File::exists(public_path($pathImg))) {
    //             $drawing = new Drawing();
    //             $drawing->setName('Imagen Producto');
    //             $drawing->setDescription($detail->product_name ?? 'Producto');
    //             $drawing->setPath(public_path($pathImg));
                
    //             // Dimensiones de la imagen dentro de la celda
    //             $drawing->setHeight(100); 
                
    //             // Asignar la coordenada dinámica (A12, A15, etc.)
    //             $drawing->setCoordinates('A' . $currentRow);
                
    //             // Centrado manual dentro de la celda
    //             $drawing->setOffsetX(10);
    //             $drawing->setOffsetY(5);

    //             $drawings[] = $drawing;
    //         }

    //         // Avanzamos el puntero de filas según el alto de las etiquetas (rowSpan)
    //         $currentRow += $rowSpan;
    //     }

    //     return $drawings;
    // }
    /**
     * Logos.
     */
    // public function drawings()
    // {
    //     $drawings = [];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Logo izquierdo
    //     |--------------------------------------------------------------------------
    //     */

    //     $logoLeft = new Drawing();

    //     $logoLeft->setName('Logo izquierdo');
    //     $logoLeft->setDescription('Logo izquierdo');
    //     $logoLeft->setPath(
    //         public_path('img/logo-izquierda.png')
    //     );

    //     $logoLeft->setHeight(65);
    //     $logoLeft->setCoordinates('A2');
    //     $logoLeft->setOffsetX(5);
    //     $logoLeft->setOffsetY(5);

    //     $drawings[] = $logoLeft;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Logo central
    //     |--------------------------------------------------------------------------
    //     */

    //     $logoCenter = new Drawing();

    //     $logoCenter->setName('Logo central');
    //     $logoCenter->setDescription('Logo central');
    //     $logoCenter->setPath(
    //         public_path('img/logo2.png')
    //     );

    //     $logoCenter->setHeight(60);
    //     $logoCenter->setCoordinates('C2');
    //     $logoCenter->setOffsetX(35);
    //     $logoCenter->setOffsetY(5);

    //     $drawings[] = $logoCenter;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Logo derecho
    //     |--------------------------------------------------------------------------
    //     */

    //     $logoRight = new Drawing();

    //     $logoRight->setName('Logo derecho');
    //     $logoRight->setDescription('Logo derecho');
    //     $logoRight->setPath(
    //         public_path('img/logo-derecha.png')
    //     );

    //     $logoRight->setHeight(60);
    //     $logoRight->setCoordinates('E2');
    //     $logoRight->setOffsetX(20);
    //     $logoRight->setOffsetY(5);

    //     $drawings[] = $logoRight;

    //     return $drawings;
    // }

    /**
     * Estilos iniciales.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            'A:H' => [
                'font' => [
                    'name' => 'Arial',
                    'size' => 10,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Frase superior
            |--------------------------------------------------------------------------
            */

            // 'A1:F1' => [
            //     'font' => [
            //         'name' => 'Arial',
            //         'size' => 10,
            //         'bold' => true,
            //         'italic' => true,
            //     ],

            //     'alignment' => [
            //         'horizontal' => Alignment::HORIZONTAL_CENTER,
            //         'vertical' => Alignment::VERTICAL_CENTER,
            //         'wrapText' => true,
            //     ],

            //     'fill' => [
            //         'fillType' => Fill::FILL_SOLID,
            //         'startColor' => [
            //             'rgb' => 'F2F2F2',
            //         ],
            //     ],
            // ],

            /*
            |--------------------------------------------------------------------------
            | Título
            |--------------------------------------------------------------------------
            */

            'A3:H3' => [
                'font' => [
                    'name' => 'Arial',
                    'size' => 16,
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '4CA746',
                    ],
                ],
            ],
        ];
    }

    /**
     * Eventos posteriores a la creación de la hoja.
     */
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $green = '4CA746';
                $white = 'FFFFFF';
                $yellow = "FFCFAF";
                $borderColor = '5E5C5C';

                /*
                |--------------------------------------------------------------------------
                | MERGES
                |--------------------------------------------------------------------------
                */

                // Frase superior

                // Título
                $sheet->mergeCells('A3:H3');

                // Información general
                $sheet->mergeCells('A4:H4');

                // Información
                $sheet->mergeCells('B5:D5');
                $sheet->mergeCells('F5:H5');

                $sheet->mergeCells('B6:D6');
                $sheet->mergeCells('F6:H6');

                $sheet->mergeCells('B7:D7');
                $sheet->mergeCells('F7:H7');

                $sheet->mergeCells('B8:D8');
                $sheet->mergeCells('F8:H8');

                $sheet->mergeCells('B9:D9');
                $sheet->mergeCells('F9:H9');

                // Detalle producción
                $sheet->mergeCells('A10:H10');

                /*
                |--------------------------------------------------------------------------
                | SECCIONES VERDES
                |--------------------------------------------------------------------------
                */

                $sectionRanges = [
                    'A3:H3',
                    'A4:H4',
                    'A10:H10',
                ];

                foreach ($sectionRanges as $range) {

                    $sheet->getStyle($range)->applyFromArray([

                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $green,
                            ],
                        ],

                        'font' => [
                            'color' => [
                                'rgb' => $white,
                            ],
                            'bold' => true,
                        ],

                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | LABELS INFORMACIÓN GENERAL
                |--------------------------------------------------------------------------
                */

                $labels = [
                    'A5',
                    'E5',
                    'A6',
                    'E6',
                    'A7',
                    'E7',
                    'A8',
                    'E8',
                    'A9',
                    'E9',
                ];

                foreach ($labels as $cell) {

                    $sheet->getStyle($cell)->applyFromArray([

                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $green,
                            ],
                        ],

                        'font' => [
                            'color' => [
                                'rgb' => $white,
                            ],
                            'bold' => true,
                        ],

                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],

                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | BORDES INFORMACIÓN GENERAL
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A5:H9')->applyFromArray([

                    'borders' => [

                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => $borderColor,
                            ],
                        ],

                    ],

                ]);

                /*
                |--------------------------------------------------------------------------
                | HEADER DETALLE
                |--------------------------------------------------------------------------
                */

                $detailHeaderRow = 11;

                $sheet->getStyle(
                    "A{$detailHeaderRow}:H{$detailHeaderRow}"
                )->applyFromArray([

                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => $green,
                        ],
                    ],

                    'font' => [
                        'color' => [
                            'rgb' => $white,
                        ],
                        'bold' => true,
                    ],

                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],

                    'borders' => [

                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => $borderColor,
                            ],
                        ],

                    ],

                ]);

                /*
                |--------------------------------------------------------------------------
                | CANTIDAD DE FILAS DE DETALLE
                |--------------------------------------------------------------------------
                */

                $detailRows = 0;

                foreach ($this->details as $detail) {
                    $labelsCount = $detail->list_labels->count();
                    $detailRows += max(1, $labelsCount);
                }

                $detailStartRow = 12;

                $detailEndRow = $detailStartRow + $detailRows - 1;

                /*
                |--------------------------------------------------------------------------
                | DETALLE
                |--------------------------------------------------------------------------
                */

                if ($detailRows > 0) {

                    $sheet->getStyle(
                        "A{$detailStartRow}:H{$detailEndRow}"
                    )->applyFromArray([

                        'borders' => [

                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => $borderColor,
                                ],
                            ],

                        ],

                        'alignment' => [

                            'vertical' => Alignment::VERTICAL_CENTER,

                            'wrapText' => true,

                        ],

                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | MERGE DE PRODUCTOS
                    |--------------------------------------------------------------------------
                    */

                    $currentRow = $detailStartRow;

                    foreach ($this->details as $detail) {
                        $pathImg = $detail->product_img;

                        if (!empty($pathImg) && File::exists(public_path($pathImg))) {
                            $drawing = new Drawing();
                            $drawing->setName('Foto Producto');
                            $drawing->setPath(public_path($pathImg));
                            $drawing->setCoordinates('A' . $currentRow);
                            // --- HACE QUE LA IMAGEM SE COMPORTE COMO PARTE DE LA CELDA ---
                            // 'oneCell' / 'twoCell' hace que al mover o redimensionar la celda, la imagen se ajuste con ella.
                            $drawing->setEditAs(\PhpOffice\PhpSpreadsheet\Worksheet\Drawing::EDIT_AS_ONECELL);

                            // Ajustes de tamaño y offsets
                            $drawing->setHeight(60);
                            $drawing->setOffsetX(20);
                            $drawing->setOffsetY(5);

                            $drawing->setWorksheet($sheet);
                        }
                        $rowSpan = max(
                            1,
                            $detail->list_labels->count()
                        );

                        if ($rowSpan > 1) {

                            $lastRow = $currentRow + $rowSpan - 1;

                            $sheet->mergeCells(
                                "A{$currentRow}:A{$lastRow}"
                            );

                            $sheet->mergeCells(
                                "B{$currentRow}:B{$lastRow}"
                            );

                            $sheet->mergeCells(
                                "C{$currentRow}:C{$lastRow}"
                            );

                            $sheet->mergeCells(
                                "D{$currentRow}:D{$lastRow}"
                            );
                            $sheet->mergeCells(
                                "H{$currentRow}:H{$lastRow}"
                            );
                        }

                        $currentRow += $rowSpan;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ALINEACIÓN PRODUCTOS
                    |--------------------------------------------------------------------------
                    */
                    $colums = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                    foreach ($colums as $column) {
                        $sheet->getStyle(
                            "{$column}{$detailStartRow}:{$column}{$detailEndRow}"
                        )
                            ->getAlignment()
                            ->setVertical(
                                Alignment::VERTICAL_CENTER
                            );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $totalRow = $detailEndRow + 1;

                $sheet->mergeCells(
                    "A{$totalRow}:F{$totalRow}"
                );
                $sheet->mergeCells(
                    "G{$totalRow}:H{$totalRow}"
                );
                $sheet->getStyle(
                    "A{$totalRow}:H{$totalRow}"
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => $borderColor,
                            ],
                        ],
                    ],
                ]);

                $sheet->getStyle(
                    "A{$totalRow}:F{$totalRow}"
                )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                /*
                |--------------------------------------------------------------------------
                | OBSERVACIONES
                |--------------------------------------------------------------------------
                */

                $observationHeaderRow = $totalRow + 1;

                $observationRow = $totalRow + 2;

                $sheet->mergeCells(
                    "A{$observationHeaderRow}:H{$observationHeaderRow}"
                );

                $sheet->mergeCells(
                    "A{$observationRow}:H{$observationRow}"
                );

                $sheet->getStyle(
                    "A{$observationHeaderRow}:H{$observationHeaderRow}"
                )->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => $green,
                        ],
                    ],
                    'font' => [
                        'color' => [
                            'rgb' => $white,
                        ],
                        'bold' => true,
                    ],
                ]);

                $sheet->getStyle(
                    "A{$observationHeaderRow}:H{$observationRow}"
                )->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => $borderColor,
                            ],
                        ],
                    ],
                ]);

                $sheet->getStyle(
                    "A{$observationRow}:H{$observationRow}"
                )
                    ->getAlignment()
                    ->setWrapText(true);

                $sheet->getRowDimension(
                    $observationRow
                )->setRowHeight(70);

                /*
                |--------------------------------------------------------------------------
                | FIRMAS
                |--------------------------------------------------------------------------
                */

                $signatureRow = $observationRow + 3;

                $sheet->mergeCells(
                    "A{$signatureRow}:B{$signatureRow}"
                );

                $sheet->mergeCells(
                    "F{$signatureRow}:G{$signatureRow}"
                );

                $sheet->getStyle(
                    "A{$signatureRow}:B{$signatureRow}"
                )->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle(
                    "F{$signatureRow}:G{$signatureRow}"
                )->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $comercialRow = $signatureRow + 1;
                $sheet->mergeCells(
                    "A{$comercialRow}:B{$comercialRow}"
                );

                $sheet->mergeCells(
                    "F{$comercialRow}:G{$comercialRow}"
                );

                $sheet->getStyle(
                    "A{$comercialRow}:H{$comercialRow}"
                )->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => $yellow,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                /*
                |--------------------------------------------------------------------------
                | ALTURAS
                |--------------------------------------------------------------------------
                */

                $sheet->getRowDimension(1)
                    ->setRowHeight(30);


                $sheet->getRowDimension(3)
                    ->setRowHeight(40);

                $sheet->getRowDimension(4)
                    ->setRowHeight(25);

                $sheet->getRowDimension(10)
                    ->setRowHeight(25);

                $sheet->getRowDimension(11)
                    ->setRowHeight(40);

                /*
                |--------------------------------------------------------------------------
                | CONFIGURACIÓN DE IMPRESIÓN
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()
                    ->setOrientation(
                        PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet->getPageSetup()
                    ->setPaperSize(
                        PageSetup::PAPERSIZE_A4
                    );

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->getPageSetup()
                    ->setFitToHeight(0);

                /*
                |--------------------------------------------------------------------------
                | MÁRGENES
                |--------------------------------------------------------------------------
                */

                $sheet->getPageMargins()
                    ->setTop(0.4);

                $sheet->getPageMargins()
                    ->setBottom(0.4);

                $sheet->getPageMargins()
                    ->setLeft(0.3);

                $sheet->getPageMargins()
                    ->setRight(0.3);

                /*
                |--------------------------------------------------------------------------
                | REPETIR ENCABEZADO AL IMPRIMIR
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()
                    ->setRowsToRepeatAtTopByStartAndEnd(
                        1,
                        11
                    );

                /*
                |--------------------------------------------------------------------------
                | FREEZE PANES
                |--------------------------------------------------------------------------
                */

                /*
                |--------------------------------------------------------------------------
                | GRIDLINES
                |--------------------------------------------------------------------------
                */

                $sheet->setShowGridlines(true);

                /*
                |--------------------------------------------------------------------------
                | CENTRAR EN PÁGINA
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()
                    ->setHorizontalCentered(true);
            },
        ];
    }
}
