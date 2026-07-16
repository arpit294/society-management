<?php

namespace App\DataTables;

use App\Models\FlatDocument;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FlatDocumentsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($model) {
                $viewUrl = route('flat-documents.show', $model->id);
                $deleteUrl = route('flat-documents.destroy', $model->id);
                
                return '<button type="button" class="btn btn-sm btn-info text-white view-btn" data-url="'.$viewUrl.'" title="View Documents">
                            <i class="fa-solid fa-eye"></i> View
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="'.$deleteUrl.'" title="Delete Submission">
                            <i class="fa-solid fa-trash"></i>
                        </button>';
            })
            ->addColumn('block', function ($model) {
                if (!$model->flat || ($model->flat && method_exists($model->flat, 'trashed') && $model->flat->trashed())) {
                    return '-';
                }
                return $model->flat && $model->flat->block ? $model->flat->block->block_name : '-';
            })
            ->addColumn('flat_no', function ($model) {
                if (!$model->flat || ($model->flat && method_exists($model->flat, 'trashed') && $model->flat->trashed())) {
                    $unitType = $model->flat ? ucwords(str_replace('_', ' ', $model->flat->unit_type ?? 'Shop')) : 'Shop';
                    if ($model->flat && $model->flat->flat_no) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>' . $model->flat->flat_no . ' (' . $unitType . ' Deleted)</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-store-slash me-1"></i>Shop Deleted</span>';
                }
                return $model->flat ? $model->flat->flat_no : '-';
            })
            ->addColumn('resident_name', function ($model) {
                return $model->user ? $model->user->name : '-';
            })
            ->addColumn('documents_count', function ($model) {
                $docs = $model->documents ?? [];
                return count($docs) . ' Documents';
            })
            ->filterColumn('block', function ($query, $keyword) {
                $query->whereHas('flat.block', function ($q) use ($keyword) {
                    $q->where('block_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('flat_no', function ($query, $keyword) {
                $query->whereHas('flat', function ($q) use ($keyword) {
                    $q->where('flat_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('resident_name', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->rawColumns(['action', 'flat_no'])
            ->setRowId('id');
    }

    public function query(FlatDocument $model): QueryBuilder
    {
        return $model->newQuery()->with(['flat.block', 'user']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('flat-documents-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->width(50),
            Column::make('block')->title(\App\Models\Setting::label('block', 'Block'))->orderable(false),
            Column::make('flat_no')->title(\App\Models\Setting::label('unit', 'Flat'))->orderable(false),
            Column::make('resident_name')->title(\App\Models\Setting::label('resident', 'Resident') . ' Name')->orderable(false),
            Column::make('resident_type')->title('Type')->render('data === "owner" ? "Owner" : "Tenant"'),
            Column::make('documents_count')->title('Uploaded Docs')->searchable(false)->orderable(false),
            Column::make('created_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'FlatDocuments_'.date('YmdHis');
    }
}
