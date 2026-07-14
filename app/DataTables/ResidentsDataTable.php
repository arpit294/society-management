<?php

namespace App\DataTables;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ResidentsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Resident>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('block', function (Resident $resident) {
                return $resident->block?->block_name;
            })
            ->editColumn('flat', function (Resident $resident) {
                if (!$resident->flat) return '-';
                $flatNo = e($resident->flat->flat_no);
                $unitType = $resident->flat->unit_type ?? 'flat';
                if (strtolower($unitType) === 'flat') {
                    return '<span class="fw-semibold">' . $flatNo . '</span>';
                }
                $badgeClass = match(strtolower($unitType)) {
                    'shop' => 'bg-warning text-dark',
                    'office' => 'bg-info text-dark',
                    'villa', 'row_house', 'rowhouse' => 'bg-primary',
                    default => 'bg-secondary'
                };
                $label = ucwords(str_replace('_', ' ', $unitType));
                return '<span class="fw-semibold">' . $flatNo . '</span> <span class="badge ' . $badgeClass . ' ms-1 px-2 py-1" style="font-size:0.7rem;">' . $label . '</span>';
            })
            ->editColumn('user', function (Resident $resident) {
                $name = e($resident->user?->name ?? '-');
                $html = '<div class="fw-bold fs-6">' . $name . '</div>';
                if ($resident->business_name) {
                    $html .= '<div class="small text-primary fw-bold mt-1"><i class="fas fa-store me-1"></i>' . e($resident->business_name) . '</div>';
                }
                return $html;
            })
            ->editColumn('created_at', function (Resident $resident) {
                return $resident->created_at?->format('d-m-Y h:i A');
            })
            ->editColumn('move_in_date', function (Resident $resident) {
                return $resident->move_in_date?->format('d-m-Y');
            })
            ->editColumn('move_out_date', function (Resident $resident) {
                return $resident->move_out_date?->format('d-m-Y');
            })
            ->editColumn('type', function (Resident $resident) {
                $class = $resident->type === 'owner' ? 'bg-primary' : 'bg-info text-dark';
                return '<span class="badge ' . $class . '">' . ucfirst($resident->type) . '</span>';
            })
            ->filterColumn('block', function ($query, $keyword) {
                $query->whereHas('block', function ($q) use ($keyword) {
                    $q->where('block_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('flat', function ($query, $keyword) {
                $query->whereHas('flat', function ($q) use ($keyword) {
                    $q->where('flat_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('user', function ($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->whereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhere('business_name', 'like', "%{$keyword}%")
                    ->orWhere('contact_person', 'like', "%{$keyword}%")
                    ->orWhere('gst_number', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', 'residents.action')
            ->rawColumns(['flat', 'user', 'type', 'action'])
            ->setRowId('id');
    }

    public function query(Resident $model): QueryBuilder
    {
        $activeResidents = Resident::where(function($q) {
                $q->whereNull('move_out_date')
                  ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            // Prioritize residents with NO move_out_date
            ->orderByRaw('move_out_date IS NOT NULL')
            // Sort by type DESC so 'rental' comes before 'owner'
            ->orderBy('type', 'desc')
            ->get();

        $primaryResidentIds = [];
        $processedFlats = [];

        foreach ($activeResidents as $resident) {
            if (!$resident->flat_id || in_array($resident->flat_id, $processedFlats)) {
                continue;
            }
            $processedFlats[] = $resident->flat_id;
            $primaryResidentIds[] = $resident->id;
        }

        return $model->newQuery()
                     ->whereIn('id', $primaryResidentIds)
                     ->with(['block', 'flat', 'user']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('residents-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->parameters([
                'dom' => '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('block')->title(\App\Models\Setting::label('block', 'Block/Wing'))->orderable(false),
            Column::make('flat')->title(\App\Models\Setting::label('unit', 'Flat') . ' No')->orderable(false),
            Column::make('user')->title('User Name')->orderable(false),
            Column::make('type')->title('Type'),
            Column::make('move_in_date')->title('Move In'),
            Column::make('move_out_date')->title('Move Out'),
            Column::make('created_at')->title('Created At'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Residents_'.date('YmdHis');
    }
}
