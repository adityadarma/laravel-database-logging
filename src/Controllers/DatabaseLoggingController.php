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
        try {
            $tables = $this->listTables();
            ksort($tables);
        } catch (\Throwable $e) {
            $tables = [];
        }
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
        $model = $request->input('model') ?: $request->input('table');
        $modelId = $request->input('model_id') ?: $request->input('id');
        $method = $request->input('method') ?: null;

        $query = DatabaseLogging::query()
            ->when($request->user, function ($query) use ($request) {
                $exp = explode('|', $request->user);
                $query->where('loggable_type', $exp[0] !== '' ? $exp[0] : null);
                $query->where('loggable_id', $exp[1] !== '' ? $exp[1] : null);
            })
            ->when($method, function ($query) use ($method) {
                $query->where('method', strtoupper($method));
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->start_date.' 00:00:00');
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->where('created_at', '<=', $request->end_date.' 23:59:59');
            });


        if ($model && $modelId !== null && $modelId !== '') {
            $tableName = (is_string($model) && class_exists($model) && is_subclass_of($model, \Illuminate\Database\Eloquent\Model::class))
                ? (new $model)->getTable()
                : $model;

            $escapedModel = is_string($model) ? str_replace('\\', '\\\\', $model) : $model;

            $query->where(function ($q) use ($tableName, $escapedModel, $modelId) {
                $q->where('data', 'like', '%"table":"' . $tableName . '","id":' . $modelId . ',%')
                    ->orWhere('data', 'like', '%"table":"' . $tableName . '","id":' . $modelId . '}%')
                    ->orWhere('data', 'like', '%"table":"' . $tableName . '","id":"' . $modelId . '"%')
                    ->orWhere('data', 'like', '%"table": "' . $tableName . '", "id": ' . $modelId . ',%')
                    ->orWhere('data', 'like', '%"table": "' . $tableName . '", "id": ' . $modelId . '}%')
                    ->orWhere('data', 'like', '%"table": "' . $tableName . '", "id": "' . $modelId . '"%')
                    ->orWhere('data', 'like', '%"id":' . $modelId . ',"table":"' . $tableName . '"%')
                    ->orWhere('data', 'like', '%"id":"' . $modelId . '","table":"' . $tableName . '"%')
                    ->orWhere('data', 'like', '%"model":"' . $escapedModel . '","id":' . $modelId . ',%')
                    ->orWhere('data', 'like', '%"model":"' . $escapedModel . '","id":' . $modelId . '}%')
                    ->orWhere('data', 'like', '%"model":"' . $escapedModel . '","id":"' . $modelId . '"%');
            });
        } elseif ($model) {
            $tableName = (is_string($model) && class_exists($model) && is_subclass_of($model, \Illuminate\Database\Eloquent\Model::class))
                ? (new $model)->getTable()
                : $model;

            $escapedModel = is_string($model) ? str_replace('\\', '\\\\', $model) : $model;

            $query->where(function ($q) use ($tableName, $escapedModel) {
                $q->where('data', 'like', '%"table":"' . $tableName . '"%')
                    ->orWhere('data', 'like', '%"table": "' . $tableName . '"%');

                if ($tableName !== $escapedModel) {
                    $q->orWhere('data', 'like', '%"model":"' . $escapedModel . '"%')
                        ->orWhere('data', 'like', '%"model": "' . $escapedModel . '"%');
                }
            });
        } elseif ($modelId !== null && $modelId !== '') {
            $query->where(function ($q) use ($modelId) {
                $q->where('data', 'like', '%"id":' . $modelId . ',%')
                    ->orWhere('data', 'like', '%"id":' . $modelId . '}%')
                    ->orWhere('data', 'like', '%"id":"' . $modelId . '"%')
                    ->orWhere('data', 'like', '%"id": ' . $modelId . ',%')
                    ->orWhere('data', 'like', '%"id": ' . $modelId . '}%')
                    ->orWhere('data', 'like', '%"id": "' . $modelId . '"%');
            });
        }

        $data['draw'] = $request->draw;
        $data['recordsTotal'] = DatabaseLogging::query()->count();

        // dot notation: $request->search['value'] blows up when DataTables
        // does not send the search parameter at all
        $search = $request->input('search.value');

        $querySearch = $query
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('data', 'like', '%' . $search . '%')
                        ->orWhere('path', 'like', '%' . $search . '%')
                        ->orWhere('user_name', 'like', '%' . $search . '%')
                        ->orWhere('ip_address', 'like', '%' . $search . '%')
                        ->orWhere('method', 'like', '%' . $search . '%');
                });
            });
        $data['recordsFiltered'] = $querySearch->count();
        $data['data'] = $querySearch
            ->offset($request->start)
            ->limit($request->length)
            ->latest()
            ->get()
            ->map(function ($item) use (&$lastIndex) {
                $lastIndex++;

                return [
                    'DT_RowIndex' => $lastIndex,
                    'DT_RowId' => "row-{$item->id}",
                    'id' => $item->id,
                    'method' => strtoupper($item->method ?? 'GET'),
                    'path' => $item->path ?? '/',
                    'host' => $item->host ?? '',
                    'user' => $item->name,
                    'ip_address' => $item->ip_address ?? '-',
                    'date' => $item->date_created ?: ($item->created_at ? $item->created_at->format('d-m-Y H:i:s') : '-'),
                    'details' => view('LaravelDatabaseLogging::table', $this->formatDetailPayload($item, $item->id ?? $lastIndex))->render(),
                ];
            });

        return response()->json($data);
    }

    /**
     * Format payload for the detail drawer view.
     *
     * @param mixed $item
     * @param int|string $rowId
     * @return array
     */
    protected function formatDetailPayload($item, $rowId): array
    {
        $host = is_object($item) ? ($item->host ?? '-') : ($item['host'] ?? '-');
        $path = is_object($item) ? ($item->path ?? '-') : ($item['path'] ?? '-');

        return [
            'log' => $item,
            'uniqueId' => $rowId ?: (is_object($item) ? ($item->id ?? uniqid('log_')) : ($item['id'] ?? uniqid('log_'))),
            'dataList' => (is_object($item) ? $item->data : ($item['data'] ?? [])) ?: [],
            'requestData' => (is_object($item) ? $item->request : ($item['request'] ?? [])) ?: [],
            'responseData' => (is_object($item) ? $item->response : ($item['response'] ?? [])) ?: [],
            'queryList' => (is_object($item) ? $item->query : ($item['query'] ?? [])) ?: [],
            'ip' => is_object($item) ? ($item->ip_address ?? '-') : ($item['ip_address'] ?? '-'),
            'host' => $host,
            'path' => $path,
            'method' => strtoupper(is_object($item) ? ($item->method ?? 'GET') : ($item['method'] ?? 'GET')),
            'userName' => is_object($item) ? ($item->name ?? null) : ($item['name'] ?? null),
            'loggableType' => is_object($item) ? ($item->loggable_type ?? null) : ($item['loggable_type'] ?? null),
            'loggableId' => is_object($item) ? ($item->loggable_id ?? null) : ($item['loggable_id'] ?? null),
            'agent' => is_object($item) ? ($item->agent ?? '-') : ($item['agent'] ?? '-'),
            'dateCreated' => is_object($item) ? ($item->date_created ?: ($item->created_at ? $item->created_at->format('d-m-Y H:i:s') : '-')) : ($item['date_created'] ?? ''),
            'fullUrl' => rtrim($host, '/') . '/' . ltrim($path, '/'),
        ];
    }
}
