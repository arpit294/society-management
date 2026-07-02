<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<User>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filterColumn('role', function (QueryBuilder $query, string $keyword): void {
                $query->where('role', $keyword);
            })
            ->filterColumn('status', function (QueryBuilder $query, string $keyword): void {
                $val = ($keyword === 'active' || $keyword === '1' || $keyword === 1 || $keyword === true) ? 1 : 0;
                $query->where('status', $val);
            })
            ->addColumn('action', 'users.action')
            ->editColumn('role', function (User $user) {
                $role = trim((string) $user->role);
                if (empty($role)) {
                    $role = $user->roles->pluck('name')->first() ?? 'N/A';
                }
                $roleLower = strtolower($role);

                if ($roleLower === 'admin') {
                    return '<span class="badge bg-danger text-white px-3 py-2 fw-bold shadow-sm" style="font-size: 0.85rem;"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>';
                }
                if (in_array($roleLower, ['committee_member', 'commitee_member'])) {
                    return '<span class="badge bg-primary text-white px-3 py-2 fw-bold shadow-sm" style="font-size: 0.85rem;"><i class="fa-solid fa-users-gear me-1"></i> Committee Member</span>';
                }
                if (in_array($roleLower, ['secretary', 'secretory'])) {
                    return '<span class="badge bg-info text-dark px-3 py-2 fw-bold shadow-sm" style="font-size: 0.85rem;"><i class="fa-solid fa-user-tie me-1"></i> Secretary</span>';
                }

                $label = config('roles.labels.'.$role, ucwords(str_replace('_', ' ', $role)));
                return '<span class="badge bg-secondary bg-opacity-25 text-body px-3 py-1 fw-semibold">'.$label.'</span>';
            })
            ->editColumn('status', function (User $user) {
                $class = $user->status === 'active' ? 'bg-success' : 'bg-danger';

                return '<span class="badge '.$class.'">'.ucfirst($user->status).'</span>';
            })
            ->editColumn('created_at', function (User $user) {
                return $user->created_at?->format('d-m-Y h:i A');
            })
            ->rawColumns(['role', 'status', 'action'])
            ->setRowId('id');

    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->with('roles');
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('id')

                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
            // Column::make('id'),
            Column::make('name'),
            Column::make('email'),
            Column::make('phone'),
            Column::make('role'),

            Column::make('status'),
            Column::make('created_at'),
            Column::make('action')->orderable(false)->searchable(false),

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_'.date('YmdHis');
    }
}
