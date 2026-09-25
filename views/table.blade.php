<div class="log-detail-container">
    <!-- Meta Summary Strip -->
    <div class="detail-summary-bar">
        <div class="summary-left">
            <span class="detail-log-id font-monospace">#{{ $uniqueId }}</span>
            <span class="detail-separator">·</span>
            <span class="detail-meta-text">{{ $dateCreated ?: '-' }}</span>
            <span class="detail-separator">·</span>
            <span class="detail-meta-text font-monospace">{{ $ip ?: '-' }}</span>
            <span class="detail-separator">·</span>
            <span class="detail-meta-url font-monospace" title="{{ $fullUrl }}">{{ $fullUrl }}</span>
        </div>
        <div class="summary-actions">
            <button type="button" class="btn-action-quiet copy-btn" data-clipboard="{{ $fullUrl }}" title="Copy full URL">
                <i class="far fa-copy mr-1"></i> Copy URL
            </button>
            @if(!empty($requestData))
                <button type="button" class="btn-action-quiet copy-btn" data-clipboard="{{ json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}" title="Copy request JSON">
                    <i class="far fa-copy mr-1"></i> Copy Payload
                </button>
            @endif
        </div>
    </div>

    <!-- Clean Line Navigation Tabs -->
    <ul class="nav nav-tabs detail-tabs" id="tab-list-{{ $uniqueId }}" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-data-link-{{ $uniqueId }}" data-toggle="tab" href="#tab-data-{{ $uniqueId }}" role="tab" aria-selected="true">
                Model Changes
                <span class="tab-count {{ count($dataList) > 0 ? 'tab-count-active' : '' }}">{{ count($dataList) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-request-link-{{ $uniqueId }}" data-toggle="tab" href="#tab-request-{{ $uniqueId }}" role="tab" aria-selected="false">
                Request
                @if(!empty($requestData))
                    <span class="tab-count tab-count-active">{{ is_countable($requestData) ? count($requestData) : 1 }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-response-link-{{ $uniqueId }}" data-toggle="tab" href="#tab-response-{{ $uniqueId }}" role="tab" aria-selected="false">
                Response
                @if(!empty($responseData))
                    <span class="tab-count">{{ is_countable($responseData) ? count($responseData) : 1 }}</span>
                @endif
            </a>
        </li>
        @if(!empty($queryList))
            <li class="nav-item">
                <a class="nav-link" id="tab-query-link-{{ $uniqueId }}" data-toggle="tab" href="#tab-query-{{ $uniqueId }}" role="tab" aria-selected="false">
                    Queries
                    <span class="tab-count">{{ count($queryList) }}</span>
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" id="tab-meta-link-{{ $uniqueId }}" data-toggle="tab" href="#tab-meta-{{ $uniqueId }}" role="tab" aria-selected="false">
                Metadata
            </a>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content detail-tab-content" id="tab-content-{{ $uniqueId }}">
        <!-- 1. MODEL CHANGES -->
        <div class="tab-pane fade show active" id="tab-data-{{ $uniqueId }}" role="tabpanel">
            @if(count($dataList) > 0)
                @foreach($dataList as $modelIndex => $itemData)
                    @php
                        $event = strtolower($itemData['event'] ?? 'update');
                        $columns = $itemData['data'] ?? [];
                        $tableName = $itemData['table'] ?? '-';
                        $recordId = $itemData['id'] ?? '-';
                        $modelClass = $itemData['model'] ?? null;
                    @endphp
                    <div class="diff-block">
                        <div class="diff-block-header">
                            <div class="diff-header-left">
                                <span class="event-tag event-{{ $event }}">{{ $event }}</span>
                                <span class="diff-table-name font-monospace">{{ $tableName }}</span>
                                @if($recordId !== '-')
                                    <span class="diff-record-id font-monospace">#{{ $recordId }}</span>
                                @endif
                                @if($modelClass)
                                    <span class="diff-model-class text-muted font-monospace small">({{ class_basename($modelClass) }})</span>
                                @endif
                            </div>
                            <div class="diff-header-right text-muted small">
                                {{ count($columns) }} {{ count($columns) === 1 ? 'attribute' : 'attributes' }} changed
                            </div>
                        </div>

                        @if(count($columns) > 0)
                            <div class="table-responsive">
                                <table class="table diff-view-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 24%;">Column</th>
                                            <th style="width: 38%;">Old Value</th>
                                            <th style="width: 38%;">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($columns as $col)
                                            @php
                                                $colName = $col['column'] ?? '-';
                                                $oldVal = $col['old'] ?? null;
                                                $newVal = $col['new'] ?? null;
                                            @endphp
                                            <tr>
                                                <td class="col-name font-monospace">{{ $colName }}</td>
                                                <!-- OLD VALUE -->
                                                <td class="diff-val diff-old">
                                                    @if($event === 'create')
                                                        <span class="diff-empty font-italic text-muted">—</span>
                                                    @elseif(is_null($oldVal))
                                                        <span class="diff-null font-monospace">null</span>
                                                    @elseif(is_bool($oldVal))
                                                        <span class="font-monospace">{{ $oldVal ? 'true' : 'false' }}</span>
                                                    @elseif(is_array($oldVal) || is_object($oldVal))
                                                        <pre class="diff-code-inline text-danger mb-0 font-monospace"><code>{{ json_encode($oldVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @elseif($oldVal === '')
                                                        <span class="diff-empty font-italic text-muted">""</span>
                                                    @else
                                                        <span class="font-monospace text-danger"><del>{{ (string) $oldVal }}</del></span>
                                                    @endif
                                                </td>
                                                <!-- NEW VALUE -->
                                                <td class="diff-val diff-new">
                                                    @if($event === 'delete')
                                                        <span class="diff-empty font-italic text-muted">—</span>
                                                    @elseif(is_null($newVal))
                                                        <span class="diff-null font-monospace">null</span>
                                                    @elseif(is_bool($newVal))
                                                        <span class="font-monospace">{{ $newVal ? 'true' : 'false' }}</span>
                                                    @elseif(is_array($newVal) || is_object($newVal))
                                                        <pre class="diff-code-inline text-success mb-0 font-monospace"><code>{{ json_encode($newVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @elseif($newVal === '')
                                                        <span class="diff-empty font-italic text-muted">""</span>
                                                    @else
                                                        <span class="font-monospace text-success font-weight-500">{{ (string) $newVal }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="diff-empty-body text-muted small p-3">
                                No attribute diffs available for this entry.
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="detail-empty-panel">
                    <p class="mb-0 text-muted small">No model changes captured during this request.</p>
                </div>
            @endif
        </div>

        <!-- 2. REQUEST PAYLOAD -->
        <div class="tab-pane fade" id="tab-request-{{ $uniqueId }}" role="tabpanel">
            @if(!empty($requestData))
                <div class="panel-box">
                    <div class="panel-box-header">
                        <span class="panel-box-title">Request Parameters & Body</span>
                        <button type="button" class="btn-action-quiet copy-btn" data-clipboard="{{ json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}">
                            <i class="far fa-copy mr-1"></i> Copy JSON
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table kv-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 28%;">Parameter</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requestData as $reqKey => $reqVal)
                                    <tr>
                                        <td class="font-monospace text-dark font-weight-500">{{ $reqKey }}</td>
                                        <td>
                                            @if(is_null($reqVal))
                                                <span class="diff-null font-monospace">null</span>
                                            @elseif(is_bool($reqVal))
                                                <span class="font-monospace">{{ $reqVal ? 'true' : 'false' }}</span>
                                            @elseif(is_array($reqVal) || is_object($reqVal))
                                                <pre class="code-box-light mb-0 font-monospace"><code>{{ json_encode($reqVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                            @elseif($reqVal === '')
                                                <span class="text-muted font-italic">""</span>
                                            @else
                                                <span class="font-monospace">{{ (string) $reqVal }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="detail-empty-panel">
                    <p class="mb-0 text-muted small">No request parameters or body captured.</p>
                </div>
            @endif
        </div>

        <!-- 3. RESPONSE -->
        <div class="tab-pane fade" id="tab-response-{{ $uniqueId }}" role="tabpanel">
            @if(!empty($responseData))
                <div class="panel-box">
                    <div class="panel-box-header">
                        <span class="panel-box-title">Response Payload</span>
                        <button type="button" class="btn-action-quiet copy-btn" data-clipboard="{{ json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}">
                            <i class="far fa-copy mr-1"></i> Copy JSON
                        </button>
                    </div>
                    <div class="p-3">
                        <pre class="code-box-editor mb-0 font-monospace"><code>{{ json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @else
                <div class="detail-empty-panel">
                    <p class="mb-0 text-muted small">No JSON response payload captured for this request.</p>
                </div>
            @endif
        </div>

        <!-- 4. QUERIES -->
        @if(!empty($queryList))
            @php
                $totalExecutionTime = array_sum(array_column($queryList, 'time'));
            @endphp
            <div class="tab-pane fade" id="tab-query-{{ $uniqueId }}" role="tabpanel">
                <div class="panel-box">
                    <div class="panel-box-header">
                        <div>
                            <span class="panel-box-title mr-2">Executed Database Queries</span>
                            <span class="text-muted small">({{ count($queryList) }} queries, {{ number_format($totalExecutionTime, 2) }} ms total)</span>
                        </div>
                        <button type="button" class="btn-action-quiet copy-btn" data-clipboard="{{ implode(";\n", array_column($queryList, 'query')) }};">
                            <i class="far fa-copy mr-1"></i> Copy All SQL
                        </button>
                    </div>
                    <div class="query-list">
                        @foreach($queryList as $qIndex => $qItem)
                            @php
                                $qTime = (float)($qItem['time'] ?? 0);
                            @endphp
                            <div class="query-item">
                                <div class="query-item-meta">
                                    <span class="query-index font-monospace">#{{ $qIndex + 1 }}</span>
                                    <span class="query-time font-monospace {{ $qTime > 50 ? 'query-slow' : '' }}">{{ number_format($qTime, 2) }} ms</span>
                                    <button type="button" class="btn-action-quiet copy-btn ml-auto" data-clipboard="{{ $qItem['query'] ?? '' }}" title="Copy query">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                                <pre class="query-code mb-0 font-monospace"><code>{{ $qItem['query'] ?? '-' }}</code></pre>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- 5. METADATA -->
        <div class="tab-pane fade" id="tab-meta-{{ $uniqueId }}" role="tabpanel">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="panel-box h-100">
                        <div class="panel-box-header">
                            <span class="panel-box-title">HTTP Request</span>
                        </div>
                        <table class="table kv-table mb-0">
                            <tbody>
                                <tr>
                                    <td class="kv-key">Method</td>
                                    <td class="kv-val"><span class="font-monospace font-weight-500">{{ $method }}</span></td>
                                </tr>
                                <tr>
                                    <td class="kv-key">Host</td>
                                    <td class="kv-val font-monospace">{{ $host }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-key">Path</td>
                                    <td class="kv-val font-monospace">{{ $path }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-key">Client IP</td>
                                    <td class="kv-val font-monospace">{{ $ip }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-key">Timestamp</td>
                                    <td class="kv-val">{{ $dateCreated }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel-box h-100">
                        <div class="panel-box-header">
                            <span class="panel-box-title">Actor & Environment</span>
                        </div>
                        <table class="table kv-table mb-0">
                            <tbody>
                                <tr>
                                    <td class="kv-key">User</td>
                                    <td class="kv-val font-weight-500">{{ $userName ?: 'Guest / System' }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-key">Loggable Type</td>
                                    <td class="kv-val font-monospace">{{ $loggableType ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-key">Loggable ID</td>
                                    <td class="kv-val font-monospace">{{ $loggableId ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="kv-key">User Agent</td>
                                    <td class="kv-val font-monospace small text-muted text-break">{{ $agent }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>