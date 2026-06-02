<?php

namespace App\DataTables;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ExpenseCategoriesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<ExpenseCategory> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('status', function (ExpenseCategory $category) {
                $class = $category->status === 'active' ? 'bg-success' : 'bg-danger';
                return '<span class="badge ' . $class . '">' . ucfirst($category->status) . '</span>';
            })
            ->editColumn('created_at', function (ExpenseCategory $category) {
                return $category->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('action', function (ExpenseCategory $category) {
                $editUrl = route('expense-categories.edit', $category->id);
                $deleteUrl = route('expense-categories.destroy', $category->id);

                return '
                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-category" data-url="' . $editUrl . '" data-title="Edit Expense Category">Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-category" data-url="' . $deleteUrl . '">Delete</button>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ExpenseCategory>
     */
    public function query(ExpenseCategory $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('expensecategories-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1, 'desc')
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('title')->title('Title'),
            Column::make('slug')->title('Slug'),
            Column::make('status')->title('Status'),
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
        return 'ExpenseCategories_' . date('YmdHis');
    }
}
