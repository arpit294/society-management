<?php

namespace Modules\Finance\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\Payment;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PaymentsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function (Payment $row) {
                return '<a href="'.route('finance.payments.receipt-pdf', $row->id).'" class="btn btn-sm btn-outline-info no-loader" title="Print Receipt" target="_blank"><i class="fa-solid fa-receipt me-1"></i> Receipt</a>';
            })
            ->addColumn('resident', function (Payment $row) {
                return $row->user ? $row->user->name : 'N/A';
            })
            ->addColumn('flat', function (Payment $row) {
                if (!$row->flat) return 'N/A';
                return ($row->flat->block ? 'Block ' . $row->flat->block->block_name . ' - ' : '') . $row->flat->flat_no;
            })
            ->addColumn('bank_account', function (Payment $row) {
                return $row->bankAccount ? $row->bankAccount->bank_name : 'Cash';
            })
            ->addColumn('amount_formatted', function (Payment $row) {
                return '<strong class="text-success">₹' . number_format($row->amount, 2) . '</strong>';
            })
            ->addColumn('mode_badge', function (Payment $row) {
                return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">' . strtoupper(str_replace('_', ' ', $row->payment_mode)) . '</span>';
            })
            ->rawColumns(['action', 'amount_formatted', 'mode_badge']);
    }

    public function query(Payment $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['user', 'flat.block', 'bankAccount', 'invoice'])
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('finance-payments-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::make('receipt_number')->title('Receipt No'),
            Column::make('payment_date')->title('Date'),
            Column::make('resident')->title('Resident'),
            Column::make('flat')->title('Property Unit'),
            Column::make('amount_formatted')->title('Amount Received'),
            Column::make('mode_badge')->title('Mode'),
            Column::make('bank_account')->title('Deposited To'),
            Column::make('transaction_reference')->title('Reference (UTR/Cheque)'),
            Column::computed('action')->title('Receipt')->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }
}
