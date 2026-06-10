<?php

namespace App\DataTables;

use App\Models\MaintenanceBill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MaintenanceDetailsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<MaintenanceBill>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('apartment', function ($bill) {
                return $bill->flat ? ($bill->flat->block->block_name ?? '-').'-'.$bill->flat->flat_no : '-';
            })
            ->addColumn('flat_type', function ($bill) {
                return $bill->flat && $bill->flat->flatType ? $bill->flat->flatType->name : '-';
            })
            ->addColumn('resident', function ($bill) {
                return $bill->user ? $bill->user->name : '-';
            })
            ->addColumn('total_cost', function ($bill) {
                return '<span class="fw-bold">₹'.number_format($bill->total_amount, 2).'</span>';
            })
            ->addColumn('status', function ($bill) {
                if ($bill->status === 'paid') {
                    return '<span class="badge bg-success">Paid</span>';
                } elseif ($bill->status === 'due') {
                    return '<span class="badge bg-danger">Due</span>';
                } else {
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                }
            })
            ->addColumn('payment_date', function ($bill) {
                return $bill->paid_at ? Carbon::parse($bill->paid_at)->format('d-m-Y') : '--';
            })
            ->addColumn('action', function ($bill) {
                $html = '<div class="d-flex gap-1 justify-content-center">';
                if ($bill->status !== 'paid') {
                    $html .= '<form action="'.route('maintenance-bills.update-status', $bill->id).'" method="POST" class="d-inline ajax-status-form">';
                    $html .= csrf_field();
                    $html .= '<input type="hidden" name="status" value="paid">';
                    $html .= '<button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Pay</button>';
                    $html .= '</form>';
                } else {
                    $html .= '<form action="'.route('maintenance-bills.update-status', $bill->id).'" method="POST" class="d-inline ajax-status-form">';
                    $html .= csrf_field();
                    $html .= '<input type="hidden" name="status" value="due">';
                    $html .= '<button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">Due</button>';
                    $html .= '</form>';
                }
                if ($bill->status === 'paid') {
                    $html .= '<a href="'.route('maintenance-bills.details', $bill->id).'" class="btn btn-sm btn-outline-primary text-nowrap"><i class="fa-solid fa-eye me-1"></i> View</a>';
                }
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['total_cost', 'status', 'action'])
            ->filterColumn('resident', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('flat_type', function($query, $keyword) {
                $query->whereHas('flat.flatType', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('apartment', function($query, $keyword) {
                $query->whereHas('flat', function($q) use ($keyword) {
                    $q->where('flat_no', 'like', "%{$keyword}%")
                      ->orWhereHas('block', function($q2) use ($keyword) {
                          $q2->where('block_name', 'like', "%{$keyword}%");
                      });
                });
            })
            ->filterColumn('total_cost', function($query, $keyword) {
                $query->where('total_amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('payment_date', function($query, $keyword) {
                // Ignore payment date search or convert formatting
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<MaintenanceBill>
     */
    public function query(MaintenanceBill $model): QueryBuilder
    {
        return $model->newQuery()->with(['user', 'flat.block', 'flat.flatType'])->where('maintenance_id', $this->id);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('maintenancedetails-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('apartment')->title('Block & Flat No.'),
            Column::make('flat_type')->title('Flat Type'),
            Column::make('resident')->title('Resident'),
            Column::make('total_cost')->title('Total Cost'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::make('payment_date')->title('Payment Date')->addClass('text-center'),
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
        return 'MaintenanceDetails_'.date('YmdHis');
    }
}
