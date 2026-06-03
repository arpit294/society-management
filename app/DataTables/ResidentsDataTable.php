<?php

namespace App\DataTables;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
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
            ->addColumn('block', function (Resident $resident) {
                return $resident->block?->block_name;
            })
            ->addColumn('flat', function (Resident $resident) {
                return $resident->flat?->flat_no;
            })
            ->addColumn('user', function (Resident $resident) {
                return $resident->user?->name;
            })
            ->editColumn('created_at', function (Resident $resident) {
                return $resident->created_at?->format('d-m-Y h:i A');
            })
            ->editColumn('move_in_date', function (Resident $resident) {
                return $resident->move_in_date?->format('d-m-Y');
            })
            ->editColumn('move_out_date', function (Resident $resident) {
                return $resident->move_out_date?->format('d-m-Y');
            })
            ->editColumn('type', function (Resident $resident) {
                return ucfirst($resident->type);
            })
            ->addColumn('action', 'residents.action')
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Resident>
     */
    public function query(Resident $model): QueryBuilder
    {
        return $model->newQuery()->with(['block', 'flat', 'user']);
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
                    ->parameters([
                        'dom' => '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::computed('block')->title('Block Name'),
            Column::computed('flat')->title('Flat No'),
            Column::computed('user')->title('User Name'),
            Column::make('type')->title('Type'),
            Column::make('move_in_date')->title('Move In'),
            Column::make('move_out_date')->title('Move Out'),
            Column::make('created_at')->title('Created At'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(120)
                  ->addClass('text-center'),
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
