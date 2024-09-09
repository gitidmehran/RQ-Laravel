<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WordExport implements FromArray,WithHeadings
{
    protected $words;

    public function __construct(array $words)
    {
        $this->words = $words;
    }

    public function array(): array
    {
        return $this->words;
    }

    public function headings(): array
    {
        return ["Ayat Id", "Word Reference", "Word"];
    }
}
