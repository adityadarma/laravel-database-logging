<?php

namespace AdityaDarma\LaravelDatabaseLogging\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use App\Http\Controllers\Controller;
use Exception;
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

        $data['logs'] =  DatabaseLogging::with(['loggable'])
            ->when($request->user, function ($query) use ($request) {
                $exp = explode('|', $request->user);
                $query->where('loggable_type', $exp[0] !== '' ? $exp[0] : null);
                $query->where('loggable_id', $exp[1] !== '' ? $exp[1] : null);
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->date_start.' 00:00:00');
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->where('created_at', '<=', $request->date_end.' 23:59:59');
            })
            ->latest()
            ->get();

        return view('LaravelDatabaseLogging::index', $data);
    }
}
