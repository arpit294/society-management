<?php

namespace App\DataTables;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ResidentsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Resident> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', function (Resident $resident) {
                return $resident->created_at?->format('d-m-Y h:i A');
            })
            ->editColumn('move_in_date', function (Resident $resident) {
                return $resident->move_in_date?->format('d-m-Y');
            })
            ->editColumn('move_out_date', function (Resident $resident) {
                return $resident->move_out_date?->format('d-m-Y');
            })
            ->addColumn('action', 'residents.action')
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Resident>
     */
    public function query(Resident $model): QueryBuilder
    {
        return $model->newQuery()->with(['user', 'flat', 'block']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('residents-table')
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
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
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
            Column::make('user.name')->title('User'),
            Column::make('block.block_name')->title('Block'),
            Column::make('flat.flat_no')->title('Flat'),
            Column::make('type'),
            Column::make('move_in_date'),
            Column::make('move_out_date'),
            Column::make('created_at'),
            Column::make('action')->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Residents_' . date('YmdHis');
    }
}
