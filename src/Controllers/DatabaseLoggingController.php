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
            // SHOW TABLES names its only column after the database
            // ("Tables_in_prod"), so the value is read positionally
            'mysql', 'mariadb' => $this->pluckNames(
                $connection->select('SHOW TABLES')
            ),
            'pgsql' => $this->pluckNames(
                $connection->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = current_schema()"),
                'tablename'
            ),
            'sqlsrv' => $this->pluckNames(
                $connection->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'"),
                'TABLE_NAME'
            ),
            'sqlite' => $this->pluckNames(
                $connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"),
                'name'
            ),
            default => throw new Exception("Database driver [$driver] tidak didukung."),
        };

        $tables = [];
        foreach ($names as $name) {
            $tables[$name] = ucwords(str_replace('_', ' ', $name));
        }

        return $tables;
    }

    /**
     * Read a table name out of every result row.
     *
     * @param array $rows
     * @param string|null $key column to read, or null to take the first value
     * @return array<int, string>
     */
    protected function pluckNames(array $rows, ?string $key = null): array
    {
        $names = [];

        foreach ($rows as $row) {
            // assigned to a variable first: reset() takes its argument by
            // reference and a cast expression cannot be passed by reference
            $values = (array) $row;

            $name = $key !== null
                ? ($values[$key] ?? null)
                : (array_values($values)[0] ?? null);

            if (is_string($name) && $name !== '') {
                $names[] = $name;
            }
        }

        return $names;
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
