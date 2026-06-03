<?php

namespace App\DataTables;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;

class MaintenanceBillsDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', 'maintenance_bills.action')
            ->editColumn('user_name', function ($row) {
                return $row->user_name;
            })
            ->editColumn('flat_no', function ($row) {
                return $row->flat_no;
            })
            ->editColumn('amount', function ($row) {
                return '<span class="fw-bold">$' . number_format($row->amount, 2) . '</span>';
            })
            ->editColumn('penalty_amount', function ($row) {
                return '<span class="badge bg-danger text-white">$' . number_format($row->penalty_amount, 2) . '</span>';
            })
            ->editColumn('total_amount', function ($row) {
                return '<span class="fw-bold text-success">$' . number_format($row->total_amount, 2) . '</span>';
            })
            ->editColumn('due_date', function ($row) {
                return $row->due_date ? date('d-m-Y', strtotime($row->due_date)) : '-';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'paid') {
                    return '<span class="badge bg-success">Paid</span>';
                }
                
                if ($row->due_date && \Carbon\Carbon::parse($row->due_date)->endOfDay()->isPast()) {
                    return '<span class="badge bg-danger">Due</span>';
                }

                return '<span class="badge bg-warning text-dark">Pending</span>';
            })
            ->rawColumns(['action', 'status', 'amount', 'penalty_amount', 'total_amount'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        $query = DB::table('maintenance_bills')
            ->join('users', 'maintenance_bills.user_id', '=', 'users.id')
            ->join('flats', 'maintenance_bills.flat_id', '=', 'flats.id')
            ->join('blocks', 'maintenance_bills.block_id', '=', 'blocks.id')
            ->select([
                'maintenance_bills.id',
                'users.name as user_name',
                'flats.flat_no as flat_no',
                'blocks.block_name as block_name',
                'maintenance_bills.amount',
                'maintenance_bills.penalty_amount',
                'maintenance_bills.total_amount',
                'maintenance_bills.due_date',
                'maintenance_bills.month',
                'maintenance_bills.year',
                'maintenance_bills.status',
            ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('maintenance-bills-table')
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
                ->name('maintenance_bills.id')
                ->title('ID')
                ->width(60)
                ->addClass('text-center'),
            Column::make('block_name')->data('block_name')->name('blocks.block_name')->title('Block'),
            Column::make('user_name')->data('user_name')->name('users.name')->title('Resident'),
            Column::make('flat_no')->data('flat_no')->name('flats.flat_no')->title('Flat No'),
            Column::make('month')->data('month')->name('maintenance_bills.month')->title('Month'),
            Column::make('year')->data('year')->name('maintenance_bills.year')->title('Year'),
            Column::make('due_date')->data('due_date')->name('maintenance_bills.due_date')->title('Due Date'),
            Column::make('amount')->data('amount')->name('maintenance_bills.amount')->title('Maintenance'),
            Column::make('penalty_amount')->data('penalty_amount')->name('maintenance_bills.penalty_amount')->title('Penalty'),
            Column::make('total_amount')->data('total_amount')->name('maintenance_bills.total_amount')->title('Total Amount'),
            Column::make('status')->data('status')->name('maintenance_bills.status')->title('Status'),
            Column::computed('action')->orderable(false)->searchable(false)->width(120),
        ];
    }

    protected function filename(): string
    {
        return 'MaintenanceBills_' . date('YmdHis');
    }
}
