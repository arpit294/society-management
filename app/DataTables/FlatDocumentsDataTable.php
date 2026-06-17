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
                return $model->flat && $model->flat->block ? $model->flat->block->block_name : '-';
            })
            ->addColumn('flat_no', function ($model) {
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
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->rawColumns(['action'])
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
            Column::make('block')->title('Block')->searchable(false)->orderable(false),
            Column::make('flat_no')->title('Flat')->searchable(false)->orderable(false),
            Column::make('resident_name')->title('Resident Name')->searchable(false)->orderable(false),
            Column::make('resident_type')->title('Type')->render('data === "owner" ? "Owner" : (data === "rental" ? "Tenant" : "Both")'),
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
