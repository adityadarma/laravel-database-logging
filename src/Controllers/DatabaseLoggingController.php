<?php

namespace AdityaDarma\LaravelDatabaseLogging\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use App\Http\Controllers\Controller;
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
        $tables_in_db = DB::select('SHOW TABLES');
        $db = "Tables_in_".env('DB_DATABASE');
        $tables = [];
        foreach($tables_in_db as $table){
            $tables[] = $table->{$db};
        }
        $data['tables'] = $tables;


        $data['logs'] =  DatabaseLogging::with(['loggable'])
            ->when($request->user, function ($query) use ($request) {
                $exp = explode('|', $request->user);
                $query->where('loggable_type', $exp[0]);
                $query->where('loggable_id', $exp[1]);
            })
            ->when($request->table, function ($query) use ($request) {
                $query->whereJsonContains('data', [['table' => $request->table]]);
            })
            ->when($request->id, function ($query) use ($request) {
                $query->whereJsonContains('data', [['id' => (int) $request->id]]);
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->date_start.' 00:00:00');
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->date_end.' 23:59:59');
            })
            ->latest()
            ->paginate(10);

        return view('LaravelDatabaseLogging::index', $data);
    }
}
