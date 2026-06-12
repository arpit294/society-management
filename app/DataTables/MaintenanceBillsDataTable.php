<?php

namespace App\DataTables;

use App\Models\MaintenanceBill;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MaintenanceBillsDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-maintenance-bill" data-url="' . route('maintenance-bills.destroy', $row->batch_id) . '" data-coreui-toggle="tooltip" title="Delete Payment Batch"><i class="fa-solid fa-trash"></i></button>';
                return '<div class="d-flex justify-content-center">' . $deleteBtn . '</div>';
            })
            ->addColumn('resident', function ($row) {
                return $row->user ? $row->user->name : 'N/A';
            })
            ->addColumn('flat', function ($row) {
                return ($row->block ? $row->block->block_name : '') . '-' . ($row->flat ? $row->flat->flat_no : '');
            })
            ->addColumn('month_year', function ($row) {
                if ($row->months_count > 1) {
                    return '<div class="d-flex flex-column gap-1">' .
                           '  <div class="text-nowrap"><i class="fa-regular fa-calendar text-muted me-1"></i> <span class="fw-medium">' . $row->start_month . '</span> <i class="fa-solid fa-arrow-right-long text-muted mx-1" style="font-size: 0.8em;"></i> <span class="fw-medium">' . $row->end_month . '</span></div>' .
                           '  <div><span class="badge bg-light text-dark border shadow-sm"><i class="fa-solid fa-layer-group text-primary me-1"></i>' . $row->months_count . ' Months Duration</span></div>' .
                           '</div>';
                }
                return '<div class="text-nowrap"><i class="fa-regular fa-calendar text-muted me-1"></i> <span class="fw-medium">' . $row->start_month . '</span></div>';
            })
            ->editColumn('amount', function ($row) {
                return '₹' . number_format($row->amount, 2);
            })
            ->editColumn('penalty_amount', function ($row) {
                return '₹' . number_format($row->penalty_amount, 2);
            })
            ->editColumn('discount_amount', function ($row) {
                return '₹' . number_format($row->discount_amount, 2);
            })
            ->editColumn('total_amount', function ($row) {
                return '₹' . number_format($row->total_amount, 2);
            })
            ->editColumn('payment_method', function ($row) {
                if (strtolower($row->payment_method) === 'upi' && $row->payment_slip) {
                    return '<a href="' . asset('storage/' . $row->payment_slip) . '" target="_blank" class="badge bg-info text-decoration-none px-3 py-2"><i class="fa-solid fa-file-invoice me-1"></i> UPI</a>';
                }
                return strtoupper($row->payment_method);
            })
            ->filterColumn('resident', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('flat', function($query, $keyword) {
                $query->whereHas('flat', function($q) use ($keyword) {
                    $q->where('flat_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('month_year', function($query, $keyword) {
                // Not perfectly filterable when grouped by subquery, but we can try
                $query->whereRaw('(SELECT CONCAT(month, " ", year) FROM maintenances WHERE id = maintenance_bills.maintenance_id) LIKE ?', ["%{$keyword}%"]);
            })
            ->rawColumns(['action', 'payment_method', 'month_year'])
            ->setRowId('batch_id');
    }

    public function query(MaintenanceBill $model): QueryBuilder
    {
        return $model->newQuery()
            ->select([
                \Illuminate\Support\Facades\DB::raw('MIN(maintenance_bills.id) as id'),
                'maintenance_bills.batch_id',
                'maintenance_bills.user_id',
                'maintenance_bills.flat_id',
                'maintenance_bills.block_id',
                'maintenance_bills.payment_method',
                'maintenance_bills.status',
                'maintenance_bills.payment_slip',
                \Illuminate\Support\Facades\DB::raw('SUM(maintenance_bills.amount) as amount'),
                \Illuminate\Support\Facades\DB::raw('SUM(maintenance_bills.penalty_amount) as penalty_amount'),
                \Illuminate\Support\Facades\DB::raw('SUM(maintenance_bills.discount_amount) as discount_amount'),
                \Illuminate\Support\Facades\DB::raw('SUM(maintenance_bills.total_amount) as total_amount'),
                \Illuminate\Support\Facades\DB::raw('COUNT(maintenance_bills.id) as months_count'),
                \Illuminate\Support\Facades\DB::raw('(SELECT CONCAT(m.month, " ", m.year) FROM maintenances m JOIN maintenance_bills mb ON mb.maintenance_id = m.id WHERE mb.batch_id = maintenance_bills.batch_id ORDER BY m.due_date ASC LIMIT 1) as start_month'),
                \Illuminate\Support\Facades\DB::raw('(SELECT CONCAT(m.month, " ", m.year) FROM maintenances m JOIN maintenance_bills mb ON mb.maintenance_id = m.id WHERE mb.batch_id = maintenance_bills.batch_id ORDER BY m.due_date DESC LIMIT 1) as end_month')
            ])
            ->with(['user', 'flat', 'block'])
            ->groupBy(
                'batch_id', 
                'maintenance_bills.user_id', 
                'maintenance_bills.flat_id', 
                'maintenance_bills.block_id', 
                'maintenance_bills.payment_method', 
                'maintenance_bills.status', 
                'maintenance_bills.payment_slip'
            );
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('maintenance-bills-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc') // Order by ID
            ->selectStyleSingle()
            ->parameters([
                'scrollX' => true,
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50)->addClass('text-nowrap'),
            Column::make('resident')->title('Resident')->name('resident')->orderable(false),
            Column::make('flat')->title('Flat')->name('flat')->orderable(false)->addClass('text-nowrap'),
            Column::make('month_year')->title('For Month')->name('month_year')->orderable(false),
            Column::make('amount')->title('Subtotal'),
            Column::make('penalty_amount')->title('Penalty'),
            Column::make('discount_amount')->title('Discount'),
            Column::make('total_amount')->title('Total'),
            Column::make('payment_method')->title('Method'),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(80)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Payments_' . date('YmdHis');
    }
}
