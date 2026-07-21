<?php

namespace App\DataTables;

use App\Helpers\CurrencyHelper;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Services\DataTable;

class FlatTypesDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', 'flat_types.action')
            ->editColumn('category_type', function ($row) {
                $map = [
                    'flat' => 'Flat / Apartment',
                    'shop' => 'Commercial Shop',
                    'villa' => 'Villa / Bungalow',
                    'rowhouse' => 'Row House',
                    'office' => 'Office Space',
                    'showroom' => 'Showroom',
                    'penthouse' => 'Penthouse',
                    'warehouse' => 'Warehouse / Godown',
                    'tenement' => 'Tenement',
                    'duplex' => 'Duplex Flat',
                    'plot' => 'Plot / Land',
                    'residential' => 'Residential',
                    'commercial' => 'Commercial'
                ];
                $val = strtolower($row->category_type ?? 'flat');
                $label = $map[$val] ?? ucfirst($val);

                [$badgeClass, $badgeStyle] = match($val) {
                    'shop', 'commercial' => ['bg-warning text-dark border border-warning', ''],
                    'office' => ['bg-info text-dark border border-info', ''],
                    'showroom' => ['bg-success text-white border border-success', ''],
                    'warehouse' => ['text-white border border-warning', 'background-color: #fd7e14 !important; color: #fff !important;'],
                    'villa', 'bungalow' => ['bg-primary text-white border border-primary', ''],
                    'rowhouse', 'row_house' => ['bg-danger text-white border border-danger', ''],
                    'tenement' => ['bg-success-subtle text-success border border-success-subtle', ''],
                    'penthouse' => ['text-white border border-secondary', 'background-color: #6f42c1 !important; color: #fff !important;'],
                    'duplex' => ['bg-info text-dark border border-info', ''],
                    'plot', 'land' => ['bg-dark text-white border border-dark', ''],
                    'flat', 'apartment', 'residential' => ['bg-secondary text-white border border-secondary', ''],
                    default => ['bg-secondary text-white border border-secondary', '']
                };

                return '<span class="badge ' . $badgeClass . ' fs-6 px-2 py-1" style="' . $badgeStyle . '">' . $label . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })
            ->editColumn('owner_maintenance_fee', function ($row) {
                return '<span class="badge bg-primary fw-bold px-3 py-2 fs-6">'.CurrencyHelper::formatCurrency($row->owner_maintenance_fee).'</span>';
            })
            ->editColumn('rental_maintenance_fee', function ($row) {
                return '<span class="badge bg-info fw-bold px-3 py-2 fs-6">'.CurrencyHelper::formatCurrency($row->rental_maintenance_fee).'</span>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge bg-success">Active</span>';
                }

                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->rawColumns(['action', 'status', 'category_type', 'owner_maintenance_fee', 'rental_maintenance_fee'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        $query = DB::table('flat_types')
            ->select([
                'id',
                'name',
                'category_type',
                'owner_maintenance_fee',
                'rental_maintenance_fee',
                'calculation_method',
                'status',
                'created_at',
            ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('flat-types-table')
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
                Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id')
                ->data('id')
                ->name('id')
                ->title('ID')
                ->width(60)
                ->addClass('text-center'),
            Column::make('name')->data('name')->name('name')->title('Category Name'),
            Column::make('category_type')->data('category_type')->name('category_type')->title('Unit Type'),
            Column::make('owner_maintenance_fee')->data('owner_maintenance_fee')->name('owner_maintenance_fee')->title('Owner Fee (Fixed)'),
            Column::make('rental_maintenance_fee')->data('rental_maintenance_fee')->name('rental_maintenance_fee')->title('Rental Fee (Fixed)'),
            Column::make('status')->data('status')->name('status'),
            Column::make('created_at')->data('created_at')->name('created_at')->title('Created At'),
            Column::computed('action')->orderable(false)->searchable(false)->width(120),
        ];
    }

    protected function filename(): string
    {
        return 'FlatTypes_'.date('YmdHis');
    }
}
