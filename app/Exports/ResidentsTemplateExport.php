<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ResidentsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'John Doe',
                'john.doe@example.com',
                '9876543210',
                '123412341234',
                'A',
                '101',
                'owner',
                '2023-01-15'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'email',
            'phone',
            'aadhar',
            'block',
            'flat',
            'type',
            'move_in_date'
        ];
    }
}
