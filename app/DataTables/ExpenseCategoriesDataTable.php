<?php

namespace App\DataTables;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class ExpenseCategoriesDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', 'expense_categories.action')
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge bg-success">Active</span>';
                }

                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        $query = DB::table('expense_categories')
            ->select([
                'id',
                'title',
                'slug',
                'status',
                'created_at',
            ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('expense-categories-table')
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
                Button::make('reload'),
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
            Column::make('title')->data('title')->name('title'),
            Column::make('slug')->data('slug')->name('slug'),
            Column::make('status')->data('status')->name('status'),
            Column::make('created_at')->data('created_at')->name('created_at')->title('Created At'),
            Column::computed('action')->orderable(false)->searchable(false)->width(120),
        ];
    }

    protected function filename(): string
    {
        return 'ExpenseCategories_'.date('YmdHis');
    }
}
