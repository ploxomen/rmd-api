<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
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

    public function __construct($order, $details)
    {
        $this->order = $order;
        $this->details = $details;
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
        ]);
    }

    /**
     * Ancho de columnas.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 40,
            'C' => 12,
            'D' => 23,
            'E' => 25,
            'F' => 20,
        ];
    }

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
            'A:F' => [
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

            'A3:F3' => [
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
                $gray = 'F2F2F2';
                $borderColor = '5E5C5C';

                /*
                |--------------------------------------------------------------------------
                | MERGES
                |--------------------------------------------------------------------------
                */

                // Frase superior

                // Título
                $sheet->mergeCells('A3:F3');

                // Información general
                $sheet->mergeCells('A4:F4');

                // Información
                $sheet->mergeCells('B5:C5');
                $sheet->mergeCells('E5:F5');

                $sheet->mergeCells('B6:C6');
                $sheet->mergeCells('E6:F6');

                $sheet->mergeCells('B7:C7');
                $sheet->mergeCells('E7:F7');

                $sheet->mergeCells('B8:C8');
                $sheet->mergeCells('E8:F8');

                $sheet->mergeCells('B9:C9');
                $sheet->mergeCells('E9:F9');

                // Detalle producción
                $sheet->mergeCells('A10:F10');

                /*
                |--------------------------------------------------------------------------
                | SECCIONES VERDES
                |--------------------------------------------------------------------------
                */

                $sectionRanges = [
                    'A3:F3',
                    'A4:F4',
                    'A10:F10',
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
                    'D5',
                    'A6',
                    'D6',
                    'A7',
                    'D7',
                    'A8',
                    'D8',
                    'A9',
                    'D9',
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

                $sheet->getStyle('A5:F9')->applyFromArray([

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
                    "A{$detailHeaderRow}:F{$detailHeaderRow}"
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
                        "A{$detailStartRow}:F{$detailEndRow}"
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
                        }

                        $currentRow += $rowSpan;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ALINEACIÓN PRODUCTOS
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        "A{$detailStartRow}:A{$detailEndRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "C{$detailStartRow}:C{$detailEndRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "D{$detailStartRow}:D{$detailEndRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "E{$detailStartRow}:E{$detailEndRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "F{$detailStartRow}:F{$detailEndRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $totalRow = $detailEndRow + 1;

                $sheet->mergeCells(
                    "A{$totalRow}:E{$totalRow}"
                );

                $sheet->getStyle(
                    "A{$totalRow}:F{$totalRow}"
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
                    "A{$totalRow}:E{$totalRow}"
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
                    "A{$observationHeaderRow}:F{$observationHeaderRow}"
                );

                $sheet->mergeCells(
                    "A{$observationRow}:F{$observationRow}"
                );

                $sheet->getStyle(
                    "A{$observationHeaderRow}:F{$observationHeaderRow}"
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
                    "A{$observationHeaderRow}:F{$observationRow}"
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
                    "A{$observationRow}:F{$observationRow}"
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
                    "D{$signatureRow}:E{$signatureRow}"
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
                    "D{$signatureRow}:E{$signatureRow}"
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