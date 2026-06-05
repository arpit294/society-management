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
                return '<span class="fw-bold">$'.number_format($bill->total_amount, 2).'</span>';
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
                $html = '';
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
                    $html .= '<a href="'.route('maintenance-bills.details', $bill->id).'" class="btn btn-sm btn-outline-info text-nowrap ms-1">View</a>';
                }

                return $html;
            })
            ->rawColumns(['total_cost', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<MaintenanceBill>
     */
    public function query(MaintenanceBill $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'flat.block', 'flat.flatType'])->where('maintenance_id', $this->id);

        if (request()->has('flat_type_id') && request('flat_type_id') != '') {
            $query->whereHas('flat', function ($q) {
                $q->where('flat_type_id', request('flat_type_id'));
            });
        }

        if (request()->has('status_filter') && request('status_filter') != '') {
            $query->where('status', request('status_filter'));
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('maintenancedetails-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) { 
                            d.flat_type_id = $("#flat-type-filter").val(); 
                            d.status_filter = $("#status-filter").val();
                        }',
            ])
            ->orderBy(4, 'asc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('apartment')->title('Block & Flat No.'),
            Column::computed('flat_type')->title('Flat Type'),
            Column::computed('resident')->title('Resident'),
            Column::computed('total_cost')->title('Total Cost'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('payment_date')->title('Payment Date')->addClass('text-center'),
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
