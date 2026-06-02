<?php

namespace App\DataTables;

use App\Models\Flat;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class FlatsDatatables extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Flat> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'flats.action')
            ->editColumn('block_id', function ($model) {
                return $model->block ? $model->block->block_name : '-';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('updated_at', function ($model) {
                return $model->updated_at ? $model->updated_at->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('status', function ($model) {
                $class = strtolower($model->status) === 'occupied' ? 'bg-success' : 'bg-danger';
                return '<span class="badge ' . $class . '">' . ucfirst($model->status) . '</span>';
            })
            ->rawColumns(['status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Flat>
     */
    public function query(Flat $model): QueryBuilder
    {
        return $model->newQuery()->with('block');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('flats-table')
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
            Column::make('id'),
            Column::make('block_id')->title('Block'),
            Column::make('flat_no'),
            Column::make('floor_no'),
            Column::make('flat_type'),
            Column::make('maintenance_amount'),
            Column::make('status'),
            Column::make('created_at'),
            Column::make('updated_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'FlatsDatatables_' . date('YmdHis');
    }
}
