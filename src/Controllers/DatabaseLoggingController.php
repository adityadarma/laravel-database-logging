<?php

namespace AdityaDarma\LaravelDatabaseLogging\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DatabaseLoggingController extends Controller
{
    public function index(Request $request): View
    {
        // User
        $data['users'] = DatabaseLogging::query()
            ->select(['loggable_type', 'loggable_id'])
            ->selectRaw('MAX(user_name) as user_name')
            ->groupBy(['loggable_type', 'loggable_id'])
            ->get();

        // Table
        $tables = $this->listTables();
        ksort($tables);
        $data['tables'] = $tables;

        return view('LaravelDatabaseLogging::index', $data);
    }

    /**
     * List the tables of the application database.
     *
     * The logged table names come from the application connection, not the
     * logging one, so this deliberately reads the default connection.
     *
     * @return array
     * @throws Exception
     */
    protected function listTables(): array
    {
        $connection = DB::connection();

        // getDriverName(), not the connection *name*: a connection may be
        // called anything ("main", "tenant", ...) while still running MySQL.
        $driver = $connection->getDriverName();

        $names = match ($driver) {
            'mysql', 'mariadb' => array_map(
                // reset() on a stdClass is deprecated as of PHP 8.1, and the
                // column of SHOW TABLES is named after the database
                static fn ($row) => reset(((array) $row)) ?: null,
                $connection->select('SHOW TABLES')
            ),
            'pgsql' => array_map(
                static fn ($row) => ((array) $row)['tablename'] ?? null,
                $connection->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = current_schema()")
            ),
            'sqlsrv' => array_map(
                static fn ($row) => ((array) $row)['TABLE_NAME'] ?? null,
                $connection->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'")
            ),
            'sqlite' => array_map(
                static fn ($row) => ((array) $row)['name'] ?? null,
                $connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")
            ),
            default => throw new Exception("Database driver [$driver] tidak didukung."),
        };

        $tables = [];
        foreach (array_filter($names) as $name) {
            $tables[$name] = ucwords(str_replace('_', ' ', $name));
        }

        return $tables;
    }

    public function datatable(Request $request): JsonResponse
    {
        $lastIndex = (int)$request->start;
        $query = DatabaseLogging::query()
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

        // dot notation: $request->search['value'] blows up when DataTables
        // does not send the search parameter at all
        $search = $request->input('search.value');

        $querySearch = $query
            ->when($search, function ($query) use ($search) {
                $query->where('data', 'like', '%'.$search.'%');
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
