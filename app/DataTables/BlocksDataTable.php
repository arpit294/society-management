<?php

namespace App\DataTables;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class BlocksDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))

            ->addColumn('action', 'blocks.action')
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })

            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        return DB::table('blocks')
            ->select(['id', 'block_name', 'total_floor', 'total_flats', 'created_at']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('blocks-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
            Column::make('block_name')->title(\App\Models\Setting::label('block', 'Block/Wing') . ' Name'),
            Column::make('total_floor')->title(\App\Models\Setting::get('society_property_type') === 'rowhouse_villa' ? 'Total Levels / Floors' : 'Total Floors / Levels'),
            Column::make('total_flats')->title('Total ' . \App\Models\Setting::label('unit_plural', 'Flats')),
            Column::make('created_at'),
            Column::make('action')->orderable(false)->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Blocks_'.date('YmdHis');
    }
}
