<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;

class ShoppingExport implements FromView, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    private $data;
    private $rowEnd = 4;
    private $rowInitial = 4;
    function __construct($data)
    {
        $this->data = $data;
    }
    public function view(): View
    {
        return view('reports.shopping', [
            'shopping' => $this->data,
        ]);
    }
    public function calculateEndRow()
    {
        foreach ($this->data as $buy) {
            if (!empty($buy->imported_expenses_cost)) {
                $this->rowEnd++;
            }
            if (!empty($buy->imported_flete_cost)) {
                $this->rowEnd++;
            }
            if (!empty($buy->imported_insurance_cost)) {

                $this->rowEnd++;
            }
            if (!empty($buy->imported_destination_cost)) {
                $this->rowEnd++;
            }
        }
    }
    public function styles(Worksheet $sheet)
    {
        $this->calculateEndRow();
        $this->rowEnd += count($this->data) + 1;
        $rango = "B" . $this->rowInitial . ":J" . $this->rowEnd;
        $title = $sheet->getStyle('B2');
        $title->getFont()->setBold(true);
        $title->getFont()->setUnderline(true);
        $title->getFont()->setSize(22);
        $title->getAlignment()->setHorizontal('center');
        $sheet->getStyle($rango)->getFont()->setSize(14);
        $sheet->getStyle($rango)->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getStyle($rango)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($rango)->getAlignment()->setVertical('center');
        //FORMATO DE MONEDA
        $sheet->getStyle("J{$this->rowInitial}:J{$this->rowEnd}")
            ->getNumberFormat()
            ->setFormatCode('"S/" #,##0.00');
    }
}
