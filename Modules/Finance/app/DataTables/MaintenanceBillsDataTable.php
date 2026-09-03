<?php

namespace Modules\Finance\DataTables;

use App\Helpers\CurrencyHelper;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\MaintenanceBill;
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
                $billIdentifier = $row->batch_id ?: $row->id;

                // If payment is paid, we can download invoice
                $downloadBtn = '';
                if ($row->status === 'paid') {
                    $downloadBtn = '<a href="'.route('maintenance-bills.download-invoice', $billIdentifier).'" class="btn btn-sm btn-outline-info me-1 no-loader" data-coreui-toggle="tooltip" title="Download Invoice" download><i class="fa-solid fa-download"></i></a>';
                }

                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-maintenance-bill" data-url="'.route('maintenance-bills.destroy', $billIdentifier).'" data-coreui-toggle="tooltip" title="Delete Payment Batch"><i class="fa-solid fa-trash"></i></button>';

                return '<div class="d-flex justify-content-center align-items-center">'.$downloadBtn.$deleteBtn.'</div>';
            })
            ->addColumn('resident', function ($row) {
                return $row->user ? $row->user->name : 'N/A';
            })
            ->addColumn('unit_type', function ($row) {
                if (!$row->flat || ($row->flat && method_exists($row->flat, 'trashed') && $row->flat->trashed())) {
                    $type = $row->flat ? ($row->flat->unit_type ?? 'shop') : 'shop';
                    $label = ucwords(str_replace('_', ' ', $type));
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>'.$label.' (Deleted)</span>';
                }
                $unitType = strtolower($row->flat->unit_type ?? 'flat');
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
                $label = ucwords(str_replace('_', ' ', $row->flat->unit_type ?? 'flat'));
                return '<span class="badge '.$badgeClass.' px-2 py-1" style="'.$badgeStyle.'">'.$label.'</span>';
            })
            ->addColumn('flat', function ($row) {
                if (!$row->flat || ($row->flat && method_exists($row->flat, 'trashed') && $row->flat->trashed())) {
                    $unitType = $row->flat ? ucwords(str_replace('_', ' ', $row->flat->unit_type ?? 'Shop')) : 'Shop';
                    if ($row->flat && $row->flat->flat_no) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>' . ($row->block ? $row->block->block_name . '-' : '') . $row->flat->flat_no . ' (' . $unitType . ' Deleted)</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>Shop Deleted</span>';
                }
                return ($row->block ? $row->block->block_name : '').'-'.($row->flat ? $row->flat->flat_no : '');
            })
            ->addColumn('month_year', function ($row) {
                if ($row->months_count > 1) {
                    return '<div class="d-flex flex-column gap-1">'.
                           '  <div class="text-nowrap"><i class="fa-regular fa-calendar text-muted me-1"></i> <span class="fw-medium">'.$row->start_month.'</span> <i class="fa-solid fa-arrow-right-long text-muted mx-1" style="font-size: 0.8em;"></i> <span class="fw-medium">'.$row->end_month.'</span></div>'.
                           '  <div><span class="badge bg-light text-dark border shadow-sm"><i class="fa-solid fa-layer-group text-primary me-1"></i>'.$row->months_count.' Months Duration</span></div>'.
                           '</div>';
                }

                return '<div class="text-nowrap"><i class="fa-regular fa-calendar text-muted me-1"></i> <span class="fw-medium">'.$row->start_month.'</span></div>';
            })
            ->editColumn('amount', function ($row) {
                return CurrencyHelper::formatCurrency($row->amount);
            })
            ->editColumn('penalty_amount', function ($row) {
                return CurrencyHelper::formatCurrency($row->penalty_amount);
            })
            ->editColumn('discount_amount', function ($row) {
                return CurrencyHelper::formatCurrency($row->discount_amount);
            })
            ->editColumn('total_amount', function ($row) {
                return CurrencyHelper::formatCurrency($row->total_amount);
            })
            ->editColumn('payment_method', function ($row) {
                if (strtolower($row->payment_method) === 'upi' && $row->payment_slip) {
                    return '<a href="'.asset('storage/'.$row->payment_slip).'" target="_blank" class="badge bg-info text-decoration-none px-3 py-2"><i class="fa-solid fa-file-invoice me-1"></i> UPI</a>';
                }

                return strtoupper($row->payment_method);
            })
            ->addColumn('received_by', function ($row) {
                return $row->receivedBy ? '<span class="fw-semibold">' . e($row->receivedBy->name) . '</span>' : '<span class="text-muted">-</span>';
            })
            ->filterColumn('received_by', function ($query, $keyword) {
                $query->whereHas('receivedBy', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('resident', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('unit_type', function ($query, $keyword) {
                if (stripos('Deleted', $keyword) !== false) {
                    $query->whereNull('flat_id')->orWhereDoesntHave('flat');
                } else {
                    $query->whereHas('flat', function ($q) use ($keyword) {
                        $q->where('unit_type', 'like', "%{$keyword}%");
                    });
                }
            })
            ->filterColumn('flat', function ($query, $keyword) {
                if (stripos('Shop Deleted', $keyword) !== false || stripos('Deleted', $keyword) !== false) {
                    $query->whereNull('flat_id')->orWhereDoesntHave('flat');
                } else {
                    $query->whereHas('flat', function ($q) use ($keyword) {
                        $q->where('flat_no', 'like', "%{$keyword}%")
                            ->orWhereHas('block', function ($q2) use ($keyword) {
                                $q2->where('block_name', 'like', "%{$keyword}%");
                            });
                    });
                }
            })
            ->filterColumn('month_year', function ($query, $keyword) {
                $query->whereRaw('(SELECT CONCAT(month, " ", year) FROM maintenances WHERE id = maintenance_bills.maintenance_id) LIKE ?', ["%{$keyword}%"]);
            })
            ->rawColumns(['action', 'payment_method', 'month_year', 'received_by', 'flat', 'unit_type'])
            ->setRowId(function ($row) {
                return $row->batch_id ?: $row->id;
            });
    }

    public function query(MaintenanceBill $model): QueryBuilder
    {
        return $model->newQuery()
            ->select([
                DB::raw('MIN(maintenance_bills.id) as id'),
                'maintenance_bills.batch_id',
                'maintenance_bills.user_id',
                'maintenance_bills.flat_id',
                'maintenance_bills.block_id',
                'maintenance_bills.payment_method',
                'maintenance_bills.status',
                'maintenance_bills.payment_slip',
                'maintenance_bills.received_by',
                DB::raw('SUM(maintenance_bills.amount) as amount'),
                DB::raw('SUM(maintenance_bills.penalty_amount) as penalty_amount'),
                DB::raw('SUM(maintenance_bills.discount_amount) as discount_amount'),
                DB::raw('SUM(maintenance_bills.total_amount) as total_amount'),
                DB::raw('COUNT(maintenance_bills.id) as months_count'),
                DB::raw('(SELECT CONCAT(m.month, " ", m.year) FROM maintenances m JOIN maintenance_bills mb ON mb.maintenance_id = m.id WHERE mb.batch_id = maintenance_bills.batch_id ORDER BY m.due_date ASC LIMIT 1) as start_month'),
                DB::raw('(SELECT CONCAT(m.month, " ", m.year) FROM maintenances m JOIN maintenance_bills mb ON mb.maintenance_id = m.id WHERE mb.batch_id = maintenance_bills.batch_id ORDER BY m.due_date DESC LIMIT 1) as end_month'),
            ])
            ->with(['user', 'flat', 'block', 'receivedBy'])
            ->groupBy(
                'batch_id',
                'maintenance_bills.user_id',
                'maintenance_bills.flat_id',
                'maintenance_bills.block_id',
                'maintenance_bills.payment_method',
                'maintenance_bills.status',
                'maintenance_bills.payment_slip',
                'maintenance_bills.received_by'
            );
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('maintenance-bills-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50)->addClass('text-nowrap'),
            Column::make('resident')->title('Resident')->name('resident')->orderable(false),
            Column::make('unit_type')->title('Structure Type')->name('unit_type')->orderable(false)->addClass('text-nowrap'),
            Column::make('flat')->title(Setting::label('block', 'Block') . ' & ' . Setting::label('unit', 'Flat'))->name('flat')->orderable(false)->addClass('text-nowrap'),
            Column::make('month_year')->title('For Month')->name('month_year')->orderable(false),
            Column::make('amount')->title('Subtotal'),
            Column::make('penalty_amount')->title('Penalty'),
            Column::make('discount_amount')->title('Discount'),
            Column::make('total_amount')->title('Total'),
            Column::make('payment_method')->title('Method'),
            Column::make('received_by')->title('Received By')->name('received_by')->orderable(false),
            Column::computed('action')->title('Action')->orderable(false)->searchable(false)->width(80)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Payments_'.date('YmdHis');
    }
}
