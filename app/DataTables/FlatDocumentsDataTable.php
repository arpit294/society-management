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
            ->addColumn('action', 'flat_documents.action')
            ->addColumn('block', function ($model) {
                return $model->flat && $model->flat->block ? $model->flat->block->block_name : '-';
            })
            ->addColumn('flat_no', function ($model) {
                return $model->flat ? $model->flat->flat_no : '-';
            })
            ->addColumn('uploaded_by_name', function ($model) {
                return $model->uploader ? $model->uploader->name : '-';
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
            ->editColumn('file_size', function ($model) {
                if (! $model->file_size) {
                    return '-';
                }
                $bytes = $model->file_size;
                $units = ['B', 'KB', 'MB', 'GB'];
                for ($i = 0; $bytes > 1024; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, 2).' '.$units[$i];
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(FlatDocument $model): QueryBuilder
    {
        return $model->newQuery()->with(['flat.block', 'uploader']);
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
            Column::make('resident_type')->title('Belongs To')->render('data === "owner" ? "Owner" : (data === "rental" ? "Tenant" : "Both")'),
            Column::make('title')->title('Document Title'),
            Column::make('file_type')->title('Type'),
            Column::make('file_size')->title('Size'),
            Column::make('uploaded_by_name')->title('Uploaded By')->searchable(false)->orderable(false),
            Column::make('created_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'FlatDocuments_'.date('YmdHis');
    }
}
