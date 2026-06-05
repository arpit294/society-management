<?php

namespace App\DataTables;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class MaintenanceBillsDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', function ($row) {
                $viewBtn = '<a href="'.route('maintenance-bills.show', $row->id).'" class="btn btn-sm btn-info me-1"><i class="fa-solid fa-eye"></i> View</a>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-maintenance-bill" data-url="'.route('maintenance-bills.destroy', $row->id).'"><i class="fa-solid fa-trash"></i> Delete</button>';

                return $viewBtn.$deleteBtn;
            })
            ->editColumn('month', function ($row) {
                return $row->month;
            })
            ->editColumn('year', function ($row) {
                return $row->year;
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'published') {
                    return '<span class="badge bg-success">Published</span>';
                }

                return '<span class="badge bg-secondary">Draft</span>';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        return DB::table('maintenances')
            ->select([
                'id',
                'month',
                'year',
                'status',
                'due_date',
            ]);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('maintenance-bills-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2, 'desc') // Order by year
            ->selectStyleSingle();
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
            Column::make('year')->data('year')->name('year')->title('Year'),
            Column::make('month')->data('month')->name('month')->title('Month'),
            Column::make('status')->data('status')->name('status')->title('Status'),
            Column::computed('action')->orderable(false)->searchable(false)->width(180),
        ];
    }

    protected function filename(): string
    {
        return 'MaintenanceBills_'.date('YmdHis');
    }
}
