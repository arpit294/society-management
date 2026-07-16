<?php

namespace App\DataTables;

use App\Helpers\CurrencyHelper;
use App\Models\NameTransferBill;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class NameTransferBillsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<NameTransferBill> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'name_transfer_bills.action')
            ->editColumn('flat_id', function ($model) {
                if (!$model->flat || ($model->flat && method_exists($model->flat, 'trashed') && $model->flat->trashed())) {
                    $unitType = $model->flat ? ucwords(str_replace('_', ' ', $model->flat->unit_type ?? 'Shop')) : 'Shop';
                    if ($model->flat && $model->flat->flat_no) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>' . ($model->flat->block->block_name ?? '') . ' ' . $model->flat->flat_no . ' (' . $unitType . ' Deleted)</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>Shop Deleted</span>';
                }
                return ($model->flat->block->block_name ?? '') . ' ' . $model->flat->flat_no;
            })
            ->addColumn('unit_type', function ($model) {
                if (!$model->flat || ($model->flat && method_exists($model->flat, 'trashed') && $model->flat->trashed())) {
                    $type = $model->flat ? ($model->flat->unit_type ?? 'shop') : 'shop';
                    $label = ucwords(str_replace('_', ' ', $type));
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>'.$label.' (Deleted)</span>';
                }
                $unitType = strtolower($model->flat->unit_type ?? 'flat');
                [$badgeClass, $badgeStyle] = match($unitType) {
                    'shop' => ['bg-warning text-dark border border-warning', ''],
                    'office' => ['bg-info text-dark border border-info', ''],
                    'showroom' => ['bg-success text-white border border-success', ''],
                    'warehouse' => ['text-white border border-warning', 'background-color: #fd7e14 !important; color: #fff !important;'],
                    'villa', 'bungalow' => ['bg-primary text-white border border-primary', ''],
                    'row_house', 'rowhouse' => ['bg-danger text-white border border-danger', ''],
                    'tenement' => ['bg-success-subtle text-success border border-success-subtle', ''],
                    'penthouse' => ['text-white border border-secondary', 'background-color: #6f42c1 !important; color: #fff !important;'],
                    'duplex' => ['bg-info text-dark border border-info', ''],
                    'plot', 'land' => ['bg-dark text-white border border-dark', ''],
                    'flat', 'apartment' => ['bg-secondary text-white border border-secondary', ''],
                    default => ['bg-secondary text-white border border-secondary', '']
                };
                $label = ucwords(str_replace('_', ' ', $model->flat->unit_type ?? 'flat'));
                return '<span class="badge '.$badgeClass.' px-2 py-1" style="'.$badgeStyle.'">'.$label.'</span>';
            })
            ->editColumn('old_owner_id', function ($model) {
                return $model->oldOwner ? $model->oldOwner->name : '-';
            })
            ->editColumn('new_owner_id', function ($model) {
                return $model->newOwner ? $model->newOwner->name : '-';
            })
            ->editColumn('amount', function ($model) {
                return '<span class="fw-bold">' . CurrencyHelper::formatCurrency($model->amount) . '</span>';
            })
            ->editColumn('status', function ($model) {
                $class = $model->status === 'paid' ? 'bg-success' : ($model->status === 'cancelled' ? 'bg-danger' : 'bg-warning');
                return '<span class="badge ' . $class . '">' . ucfirst($model->status) . '</span>';
            })
            ->addColumn('approval', function ($model) {
                if ($model->is_approved) {
                    return '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Approved</span>';
                }
                if ($model->status !== 'paid') {
                    return '<span class="badge bg-warning text-dark"><i class="fa-solid fa-lock me-1"></i>Payment Required</span>';
                }
                return '<span class="badge bg-info text-dark"><i class="fa-regular fa-clock me-1"></i>Pending Approval</span>';
            })
            ->addColumn('approved_by', function ($model) {
                return $model->approvedBy ? '<span class="fw-semibold">' . e($model->approvedBy->name) . '</span>' : '<span class="text-muted">-</span>';
            })
            ->editColumn('paid_at', function ($model) {
                return $model->paid_at ? $model->paid_at->format('d M Y h:i A') : '-';
            })
            ->rawColumns(['amount', 'status', 'approval', 'approved_by', 'action', 'flat_id', 'unit_type'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<NameTransferBill>
     */
    public function query(NameTransferBill $model): QueryBuilder
    {
        return $model->newQuery()->with(['flat.block', 'oldOwner', 'newOwner', 'approvedBy'])->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('nametransferbills-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('print'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::make('unit_type')->title('Structure Type')->name('unit_type')->orderable(false),
            Column::make('flat_id')->title(\App\Models\Setting::label('unit', 'Flat')),
            Column::make('old_owner_id')->title('Old Owner'),
            Column::make('new_owner_id')->title('New Owner'),
            Column::make('amount')->title('Amount'),
            Column::make('status')->title('Payment'),
            Column::make('approval')->title('Approval'),
            Column::make('approved_by')->title('Approved By'),
            Column::make('paid_at')->title('Paid At'),
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
        return 'NameTransferBills_' . date('YmdHis');
    }
}
