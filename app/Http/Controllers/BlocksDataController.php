<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class BlocksDataController extends Controller
{
    public function index()
    {
        return view('blocks.index');
    }

    public function data()
    {
        return DataTables::of(
            DB::table('blocks')->select(['id', 'block_name', 'total_floor', 'total_flats', 'created_at'])->orderByDesc('id')
        )
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $editUrl = route('blocks.edit', $row->id);
                $deleteUrl = route('blocks.destroy', $row->id);

                return view('blocks.action', compact('editUrl', 'deleteUrl', 'row'))->render();
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
