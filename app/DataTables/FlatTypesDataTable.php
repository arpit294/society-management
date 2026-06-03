<?php

namespace App\DataTables;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;

class FlatTypesDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', 'flat_types.action')
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })
            ->editColumn('maintenance_fee', function ($row) {
                return '<span class="badge bg-primary fw-bold px-3 py-2 fs-6">$' . number_format($row->maintenance_fee, 2) . '</span>';
            })
            ->editColumn('penalty_per_day', function ($row) {
                return '<span class="badge bg-danger text-white fw-bold px-3 py-2 fs-6">$' . number_format($row->penalty_per_day, 2) . '</span>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->rawColumns(['action', 'status', 'maintenance_fee', 'penalty_per_day'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        $query = DB::table('flat_types')
            ->select([
                'id',
                'name',
                'maintenance_fee',
                'penalty_per_day',
                'description',
                'status',
                'created_at'
            ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('flat-types-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id')
                ->data('id')
                ->name('id')
                ->title('ID')
                ->width(60)
                ->addClass('text-center'),
            Column::make('name')->data('name')->name('name'),
            Column::make('maintenance_fee')->data('maintenance_fee')->name('maintenance_fee')->title('Maintenance Fee'),
            Column::make('penalty_per_day')->data('penalty_per_day')->name('penalty_per_day')->title('Penalty / Day'),
            Column::make('description')->data('description')->name('description'),
            Column::make('status')->data('status')->name('status'),
            Column::make('created_at')->data('created_at')->name('created_at')->title('Created At'),
            Column::computed('action')->orderable(false)->searchable(false)->width(120),
        ];
    }

    protected function filename(): string
    {
        return 'FlatTypes_' . date('YmdHis');
    }
}
