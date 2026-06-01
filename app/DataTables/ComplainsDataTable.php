<?php

namespace App\DataTables;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use \Illuminate\Support\Facades\DB;

class ComplainsDataTable extends DataTable
{
    public function dataTable($query): QueryDataTable
    {
        return (new QueryDataTable($query))
            ->addColumn('action', 'complains.action')
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->where('complains.category', 'like', "%{$keyword}%");
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(): QueryBuilder
    {
        $query = DB::table('complains')
            ->join('users', 'complains.user_id', '=', 'users.id')
            ->select([
                'complains.id',
                'complains.subject',
                'complains.category',
                'users.name as user_name',
                'complains.created_at'
            ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('complains-table')
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
                Button::make('reload')
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id')
                ->data('id')
                ->name('complains.id')
                ->title('ID')
                ->width(60)
                ->addClass('text-center'),
            Column::make('subject')->data('subject')->name('complains.subject'),
            Column::make('user_name')->data('user_name')->name('users.name')->title('User'),
            Column::make('category')->data('category')->name('complains.category'),
            Column::make('created_at')->data('created_at')->name('complains.created_at')->title('Created At'),
            Column::computed('action')->orderable(false)->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Complains_' . date('YmdHis');
    }
}
