<?php

namespace App\Exports;

use App\Models\Resident;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class ResidentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    //  Define the query to fetch residents based on the request parameters
    public function query(): Builder|EloquentBuilder|Relation|ScoutBuilder
    {
        $query = Resident::query()->with(['user', 'block', 'flat']);

        if ($this->request->filled('block')) {
            $query->whereHas('block', function ($q) {
                $q->where('block_name', $this->request->block);
            });
        }
        if ($this->request->filled('type')) {
            $query->where('type', $this->request->type);
        }

        return $query;
    }

    //  Define the headings for the exported Excel file
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Aadhar ID',
            'Block Name',
            'Flat No',
            'Type',
            'Move In Date',
            'Move Out Date'
        ];
    }

    //  Map the data for each resident to the desired format for export
    public function map(mixed $resident): array
    {
        return [
            $resident->user->name ?? 'N/A',
            $resident->user->email ?? 'N/A',
            $resident->user->phone ?? 'N/A',
            $resident->user->aadhar_id ?? 'N/A',
            $resident->block->block_name ?? 'N/A',
            $resident->flat->flat_no ?? 'N/A',
            ucfirst($resident->type),
            $resident->move_in_date ? date('Y-m-d', strtotime($resident->move_in_date)) : '',
            $resident->move_out_date ? date('Y-m-d', strtotime($resident->move_out_date)) : '',
        ];
    }
}
