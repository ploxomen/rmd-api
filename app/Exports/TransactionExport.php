<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionExport implements FromView, ShouldAutoSize, WithStyles
{
    private $data;
    private $title;
    private $rowInitial = 4;
    private $rowEnd = 3;
    function __construct($data, $title)
    {
        $this->title = $title;
        $this->data = $data;

    }
    public function view(): View
    {
        return view('reports.transactions', [
            'transactions' => $this->data,
            'title' => $this->title
        ]);
    }
    public function styles(Worksheet $sheet)
    {
        $this->rowEnd += count($this->data) + 1;
        $rango = "B" . $this->rowInitial . ":Q" . $this->rowEnd;
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
        $sheet->getStyle("N{$this->rowInitial}:Q{$this->rowEnd}")
            ->getNumberFormat()
            ->setFormatCode('"S/" #,##0.00');
    }
}
