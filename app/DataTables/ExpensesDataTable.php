<?php

namespace App\DataTables;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ExpensesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Expense> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'expenses.action')
            ->editColumn('total_amount', function ($model) {
                return '$' . number_format($model->total_amount, 2);
            })
            ->editColumn('user_id', function ($model) {
                return $model->user ? $model->user->name : '-';
            })
            ->editColumn('category_id', function ($model) {
                return $model->category ? $model->category->title : '-';
            })
            ->editColumn('invoice', function ($model) {
                if ($model->invoice) {
                    $url = asset('uploads/invoices/' . $model->invoice);
                    return '<a href="' . $url . '" target="_blank" class="badge bg-info text-decoration-none">📄 View Bill</a>';
                }
                return '<span class="badge bg-secondary">No Bill</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i:s') : '-';
            })
            ->rawColumns(['invoice', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Expense>
     */
    public function query(Expense $model): QueryBuilder
    {
        return $model->newQuery()->with(['user', 'category']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('expenses-table')
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
            Column::make('title'),
            Column::make('total_amount'),
            Column::make('category_id')->title('Category'),
            Column::make('user_id')->title('Logged By'),
            Column::make('invoice'),
            Column::make('created_at'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Expenses_' . date('YmdHis');
    }
}
