<?php

namespace AdityaDarma\LaravelDatabaseLogging\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DatabaseLoggingController extends Controller
{
    public function index(Request $request): View
    {
        // User
        $data['users'] = DatabaseLogging::with(['loggable'])
            ->select(['loggable_type', 'loggable_id'])
            ->groupBy(['loggable_type', 'loggable_id'])
            ->get();

        // Table
        $connection = config('database.default');
        $tables = [];
        switch ($connection) {
            case 'mysql':
            case 'mariadb':
                $tables_in_db = DB::select("SHOW TABLES");
                foreach ($tables_in_db as $table) {
                    $tables[reset($table)] = ucwords(str_replace('_', ' ', reset($table)));
                }
                break;

            case 'pgsql':
                $tables_in_db = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
                foreach ($tables_in_db as $table) {
                    $tables[$table->tablename] = ucwords(str_replace('_', ' ', $table->tablename));
                }
                break;

            case 'sqlsrv':
                $tables_in_db = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
                foreach ($tables_in_db as $table) {
                    $tables[$table->TABLE_NAME] = ucwords(str_replace('_', ' ', $table->TABLE_NAME));
                }
                break;

            case 'sqlite':
                $tables_in_db = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tables_in_db as $table) {
                    $tables[$table->name] = ucwords(str_replace('_', ' ', $table->name));
                }
                break;

            default:
                throw new Exception("Database driver tidak didukung.");
        }
        ksort($tables);
        $data['tables'] = $tables;

        return view('LaravelDatabaseLogging::index', $data);
    }

    public function datatable(Request $request): JsonResponse
    {
        $lastIndex = (int)$request->start;
        $query =  DatabaseLogging::with(['loggable'])
            ->when($request->user, function ($query) use ($request) {
                $exp = explode('|', $request->user);
                $query->where('loggable_type', $exp[0] !== '' ? $exp[0] : null);
                $query->where('loggable_id', $exp[1] !== '' ? $exp[1] : null);
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->start_date.' 00:00:00');
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->where('created_at', '<=', $request->end_date.' 23:59:59');
            });
        $data['draw'] = $request->draw;
        $data['recordsTotal'] = $query->count();

        $querySearch = $query
            ->when($request->search['value'], function ($query) use ($request) {
                $query->where('data', 'like', '%'.$request->search['value'].'%');
            });
        $data['recordsFiltered'] = $querySearch->count();
        $data['data'] = $querySearch
            ->offset($request->start)
            ->limit($request->length)
            ->latest()
            ->get()
            ->map(function ($item) use (&$lastIndex) {
                $lastIndex++;
                $table =  view('LaravelDatabaseLogging::table', ['log' => $item])->render();
                return [
                    'DT_RowIndex' => $lastIndex,
                    'DT_RowAttr' => [
                        'data-toggle' => "collapse",
                        'data-target' => "#collapse$lastIndex",
                        'aria-expanded' => "true",
                        'aria-controls' => "collapse$lastIndex"
                    ],
                    'user' => $item->name,
                    'content' => "
                        <b>Date:</b> $item->date_created<br>
                        <b>IP:</b> $item->ip_address<br>
                        <b>Agent:</b> $item->agent<br>
                        <b>Host:</b> $item->host<br>
                        <b>Path:</b> $item->path<br>
                        <b>Method:</b> $item->method<br>
                        <table class='w-100'>
                            <td colspan='5' id='collapse$lastIndex' class='collapse acc' data-parent=''#accordion'>
                                $table
                            </td>
                        </table>
                    ",
                ];
            });

        return response()->json($data);
    }
}
