<?php

namespace Modules\Finance\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\Invoice;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InvoicesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function (Invoice $row) {
                $viewBtn = '<a href="'.route('finance.invoices.show', $row->id).'" class="btn btn-sm btn-outline-primary me-1" title="View Invoice"><i class="fa-solid fa-eye"></i></a>';
                
                $payBtn = '';
                if ($row->status !== 'paid' && $row->status !== 'cancelled') {
                    $payBtn = '<button type="button" class="btn btn-sm btn-outline-success me-1 btn-collect-payment" data-id="'.$row->id.'" data-balance="'.$row->balance_due.'" data-number="'.$row->invoice_number.'" title="Collect Payment"><i class="fa-solid fa-hand-holding-dollar"></i></button>';
                }

                $pdfBtn = '<a href="'.route('finance.invoices.pdf', $row->id).'" class="btn btn-sm btn-outline-info me-1 no-loader" title="Download PDF" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>';

                return '<div class="d-flex justify-content-center align-items-center">'.$viewBtn.$payBtn.$pdfBtn.'</div>';
            })
            ->addColumn('resident', function (Invoice $row) {
                return $row->user ? $row->user->name : 'N/A';
            })
            ->addColumn('flat', function (Invoice $row) {
                if (!$row->flat) return 'N/A';
                return ($row->flat->block ? 'Block ' . $row->flat->block->block_name . ' - ' : '') . $row->flat->flat_no;
            })
            ->addColumn('total_amount_formatted', function (Invoice $row) {
                return '₹' . number_format($row->total_amount, 2);
            })
            ->addColumn('paid_amount_formatted', function (Invoice $row) {
                return '₹' . number_format($row->paid_amount, 2);
            })
            ->addColumn('balance_due_formatted', function (Invoice $row) {
                return '<strong class="text-danger">₹' . number_format($row->balance_due, 2) . '</strong>';
            })
            ->addColumn('status_badge', function (Invoice $row) {
                return match($row->status) {
                    'paid' => '<span class="badge bg-success">Paid</span>',
                    'partially_paid' => '<span class="badge bg-warning text-dark">Partially Paid</span>',
                    'unpaid' => '<span class="badge bg-danger">Unpaid</span>',
                    'overdue' => '<span class="badge bg-danger text-white">Overdue</span>',
                    'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
                    default => '<span class="badge bg-light text-dark">'.$row->status.'</span>',
                };
            })
            ->rawColumns(['action', 'balance_due_formatted', 'status_badge']);
    }

    public function query(Invoice $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'flat.block']);

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('month')) {
            $query->where('bill_month', request('month'));
        }

        if (request()->filled('year')) {
            $query->where('bill_year', request('year'));
        }

        return $query->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('finance-invoices-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::make('invoice_number')->title('Invoice No'),
            Column::make('resident')->title('Resident'),
            Column::make('flat')->title('Property Unit'),
            Column::make('invoice_date')->title('Date'),
            Column::make('due_date')->title('Due Date'),
            Column::make('total_amount_formatted')->title('Total'),
            Column::make('paid_amount_formatted')->title('Paid'),
            Column::make('balance_due_formatted')->title('Balance Due'),
            Column::make('status_badge')->title('Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(140)->addClass('text-center'),
        ];
    }
}
