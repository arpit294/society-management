<?php

namespace App\DataTables;

use App\Helpers\CurrencyHelper;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class ExpensesDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', 'expenses.action')
            ->editColumn('expense_date', function ($row) {
                return $row->expense_date ? date('M Y', strtotime($row->expense_date)) : '-';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })
            ->editColumn('total_amount', function ($row) {
                return '<span class="badge bg-success fw-bold px-3 py-2 fs-6">' . CurrencyHelper::formatCurrency($row->total_amount) . '</span>';
            })
            ->editColumn('invoice', function ($row) {
                if ($row->invoice) {
                    $url = asset('uploads/invoices/' . $row->invoice);
                    $ext = strtolower(pathinfo($row->invoice, PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        return '<a href="' . $url . '" target="_blank"><img src="' . $url . '" alt="Invoice" class="img-thumbnail" style="height: 50px; width: 50px; object-fit: cover;"></a>';
                    } else {
                        // For PDF or others
                        return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-file-pdf me-1"></i> View PDF</a>';
                    }
                }

                return '<span class="text-muted"><i class="fa-solid fa-minus"></i></span>';
            })

            ->filterColumn('category_name', function ($query, $keyword) {
                $query->where('expense_categories.title', 'like', "%{$keyword}%");
            })
            ->filterColumn('user_name', function ($query, $keyword) {
                $query->where('users.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('expense_date', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereRaw("DATE_FORMAT(expenses.expense_date, '%b %Y') like ?", ["%{$keyword}%"])
                        ->orWhereRaw("DATE_FORMAT(expenses.expense_date, '%Y-%m') like ?", ["%{$keyword}%"]);
                });
            })
            ->filterColumn('expense_month', function ($query, $keyword) {
                if (empty($keyword)) {
                    return;
                }

                // keyword is expected as YYYY-MM from the <input type="month">
                $query->whereRaw("DATE_FORMAT(expenses.expense_date, '%Y-%m') = ?", [$keyword]);
            })
            ->rawColumns(['action', 'invoice', 'total_amount'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        // Notice we join users and expense_categories
        $query = DB::table('expenses')
            ->leftJoin('users', 'expenses.user_id', '=', 'users.id')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select([
                'expenses.id',
                'expenses.title',
                'expense_categories.title as category_name',
                'users.name as user_name',
                'expenses.total_amount',
                'expenses.invoice',
                'expenses.expense_date',
                'expenses.created_at',
            ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('expenses-table')
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
                ->name('expenses.id')
                ->title('ID')
                ->width(60)
                ->addClass('text-center'),
            Column::make('title')->data('title')->name('expenses.title'),
            Column::make('category_name')->data('category_name')->name('expense_categories.title')->title('Category'),
            Column::make('user_name')->data('user_name')->name('users.name')->title('User'),
            Column::make('expense_date')->data('expense_date')->name('expenses.expense_date')->title('Expense Month'),
            Column::make('total_amount')->data('total_amount')->name('expenses.total_amount')->title('Amount'),
            Column::make('invoice')->data('invoice')->name('expenses.invoice')->title('Invoice'),
            Column::make('created_at')->data('created_at')->name('expenses.created_at')->title('Created At'),
            Column::computed('action')->orderable(false)->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Expenses_' . date('YmdHis');
    }
}
