<?php

namespace Modules\Finance\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\VendorBill;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorBillsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function (VendorBill $row) {
                $voucherBtn = '';
                if ($row->status !== 'paid' && $row->status !== 'cancelled') {
                    $voucherBtn = '<button type="button" class="btn btn-sm btn-outline-success me-1 btn-create-voucher" data-id="'.$row->id.'" data-balance="'.$row->balance_due.'" data-vendor="'.$row->vendor?->name.'" data-bill="'.$row->bill_number.'" title="Create Payment Voucher"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Pay</button>';
                }

                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-bill" data-url="'.route('finance.vendor-bills.destroy', $row->id).'" title="Delete Bill"><i class="fa-solid fa-trash"></i></button>';

                return '<div class="d-flex justify-content-center align-items-center">'.$voucherBtn.$deleteBtn.'</div>';
            })
            ->addColumn('vendor_name', function (VendorBill $row) {
                return $row->vendor ? $row->vendor->name : 'N/A';
            })
            ->addColumn('service_type', function (VendorBill $row) {
                return $row->vendor ? '<span class="badge bg-secondary">'.$row->vendor->service_type.'</span>' : 'N/A';
            })
            ->addColumn('expense_head', function (VendorBill $row) {
                return $row->expenseAccount ? $row->expenseAccount->name : 'N/A';
            })
            ->addColumn('total_amount_formatted', function (VendorBill $row) {
                return '₹' . number_format($row->total_amount, 2);
            })
            ->addColumn('paid_amount_formatted', function (VendorBill $row) {
                return '₹' . number_format($row->paid_amount, 2);
            })
            ->addColumn('balance_due_formatted', function (VendorBill $row) {
                return '<strong class="text-danger">₹' . number_format($row->balance_due, 2) . '</strong>';
            })
            ->addColumn('status_badge', function (VendorBill $row) {
                return match($row->status) {
                    'paid' => '<span class="badge bg-success">Paid</span>',
                    'partially_paid' => '<span class="badge bg-warning text-dark">Partially Paid</span>',
                    'unpaid' => '<span class="badge bg-danger">Unpaid</span>',
                    'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
                    default => '<span class="badge bg-light text-dark">'.$row->status.'</span>',
                };
            })
            ->rawColumns(['action', 'service_type', 'balance_due_formatted', 'status_badge']);
    }

    public function query(VendorBill $model): QueryBuilder
    {
        return $model->newQuery()->with(['vendor', 'expenseAccount'])->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('finance-vendor-bills-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::make('bill_number')->title('Bill No'),
            Column::make('vendor_name')->title('Vendor / Contractor'),
            Column::make('service_type')->title('Service Category'),
            Column::make('expense_head')->title('Expense Head'),
            Column::make('bill_date')->title('Bill Date'),
            Column::make('due_date')->title('Due Date'),
            Column::make('total_amount_formatted')->title('Total Amount'),
            Column::make('paid_amount_formatted')->title('Paid'),
            Column::make('balance_due_formatted')->title('Balance Due'),
            Column::make('status_badge')->title('Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(130)->addClass('text-center'),
        ];
    }
}
