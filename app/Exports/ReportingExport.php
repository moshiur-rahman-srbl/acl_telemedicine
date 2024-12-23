<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportingExport implements FromCollection,WithHeadings,WithMapping
{
    use Exportable;

    public function __construct(array $search)
    {
        $this->search = $search;
    }
    public function collection(){
        $transaction = Transaction::getCustomTransaction($this->search);
//        dd($transaction);
        return $transaction;
    }
    public function headings(): array
    {
        return ['Transaction ID', 'Order ID', 'Merchant Share', 'Currency', 'Payment Method', 'Date&Time'];
    }
    public function map($invoice): array
    {
        return [
            $invoice->id,
            $invoice->id,
            number_format($invoice->gross - (($invoice->gross) * (config('constants.defines.MERCHANT_COMMISSION') / 100)), 2),
//            Date::dateTimeToExcel($invoice->created_at),
            $invoice->currency,
            $invoice->activity_title,
            date("d.m.Y - H:i", strtotime($invoice->created_at))
        ];
    }
//    public function columnFormats(): array
//    {
//        return [
//            'A1' => NumberFormat::FORMAT_DATE_DDMMYYYY,
//            'B1' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
//        ];
//    }

}
