<?php

namespace Modules\Finance\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\PaymentVoucher;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VouchersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function (PaymentVoucher $row) {
                $approveBtn = '';
                if ($row->approval_status === 'draft' || $row->approval_status === 'submitted') {
                    $approveBtn = '<button type="button" class="btn btn-sm btn-outline-success me-1 btn-approve-voucher" data-id="'.$row->id.'" title="Approve Voucher"><i class="fa-solid fa-check"></i></button>';
                }

                $disburseBtn = '';
                if ($row->approval_status === 'approved') {
                    $disburseBtn = '<button type="button" class="btn btn-sm btn-success text-white me-1 btn-disburse-voucher" data-id="'.$row->id.'" data-amount="'.$row->amount.'" data-number="'.$row->voucher_number.'" title="Disburse Payment"><i class="fa-solid fa-money-bill-transfer me-1"></i> Disburse</button>';
                }

                $pdfBtn = '<a href="'.route('finance.vouchers.pdf', $row->id).'" class="btn btn-sm btn-outline-info me-1 no-loader" title="Print Voucher" target="_blank"><i class="fa-solid fa-print"></i></a>';

                return '<div class="d-flex justify-content-center align-items-center">'.$approveBtn.$disburseBtn.$pdfBtn.'</div>';
            })
            ->addColumn('vendor_name', function (PaymentVoucher $row) {
                return $row->vendor ? $row->vendor->name : 'N/A';
            })
            ->addColumn('bank_account', function (PaymentVoucher $row) {
                return $row->bankAccount ? $row->bankAccount->bank_name : 'Cash';
            })
            ->addColumn('amount_formatted', function (PaymentVoucher $row) {
                return '<strong class="text-danger">₹' . number_format($row->amount, 2) . '</strong>';
            })
            ->addColumn('status_badge', function (PaymentVoucher $row) {
                return match($row->approval_status) {
                    'paid' => '<span class="badge bg-success">Disbursed / Paid</span>',
                    'approved' => '<span class="badge bg-info text-dark">Approved</span>',
                    'submitted' => '<span class="badge bg-warning text-dark">Pending Approval</span>',
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    default => '<span class="badge bg-light text-dark">'.$row->approval_status.'</span>',
                };
            })
            ->rawColumns(['action', 'amount_formatted', 'status_badge']);
    }

    public function query(PaymentVoucher $model): QueryBuilder
    {
        return $model->newQuery()->with(['vendor', 'bankAccount', 'bill', 'creator', 'approver'])->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('finance-vouchers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::make('voucher_number')->title('Voucher No'),
            Column::make('voucher_date')->title('Date'),
            Column::make('vendor_name')->title('Payee / Vendor'),
            Column::make('amount_formatted')->title('Amount'),
            Column::make('payment_mode')->title('Mode'),
            Column::make('bank_account')->title('Paying Account'),
            Column::make('reference_no')->title('Reference (Cheque/UTR)'),
            Column::make('status_badge')->title('Approval Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(140)->addClass('text-center'),
        ];
    }
}
