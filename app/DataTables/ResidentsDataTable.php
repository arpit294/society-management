<?php

namespace App\DataTables;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ResidentsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Resident> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('block', function (Resident $resident) {
                return $resident->block?->block_name;
            })
            ->addColumn('flat', function (Resident $resident) {
                return $resident->flat?->flat_no;
            })
            ->addColumn('user', function (Resident $resident) {
                return $resident->user?->name;
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
            ->filterColumn('block', function($query, $keyword) {
                $query->whereHas('block', function($q) use ($keyword) {
                    $q->where('block_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('flat', function($query, $keyword) {
                $query->whereHas('flat', function($q) use ($keyword) {
                    $q->where('flat_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('user', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', 'residents.action')
            ->rawColumns(['type', 'action'])
            ->setRowId('id');
    }

    public function query(Resident $model): QueryBuilder
    {
        // Get all currently active residents
        $activeResidents = Resident::where(function($q) {
                $q->whereNull('move_out_date')
                  ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
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
            Column::make('block')->title('Block Name')->orderable(false),
            Column::make('flat')->title('Flat No')->orderable(false),
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
        return 'Residents_' . date('YmdHis');
    }
}
