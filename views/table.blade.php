<div class="row">
    <div class="col-md-12">
        <h5 class="text-uppercase">Data</h5>
        <hr>
    </div>
    @foreach($log['data'] as $data)
        <div class="col-md-12">
            <div class="card mb-3">
                <table class="table-borderless w-100" style="background-color: #454d55; border-color: #454d55; color: #ffffff">
                    <tbody>
                        <tr>
                            <td class="fs-4"><b>Table :</b> {{ $data['table'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><b>Event :</b> {{ $data['event'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><b>ID :</b> {{ $data['id'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="w-100">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 30%">Column</th>
                            <th style="min-width: 100px">Old</th>
                            <th style="min-width: 100px">New</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(isset($data['data']))
                        @foreach($data['data'] as $column)
                            <tr>
                                @php($old = $column['old'] ?? null)
                                @php($new = $column['new'] ?? null)
                                <td>{{ $column['column'] ?? '-' }}</td>

                                @if (is_array($old) || is_object($old))
                                    <td>{!! "<pre>". e(print_r($old, true)) ."</pre>" !!}</td>
                                @else
                                    <td>{{ $old }}</td>
                                @endif

                                @if (is_array($new) || is_object($new))
                                    <td>{!! "<pre>". e(print_r($new, true)) ."</pre>" !!}</td>
                                @else
                                    <td>{{ $new }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>

<div class="row pt-5">
    <div class="col-md-12">
        <h5 class="text-uppercase">Request</h5>
        <hr>
    </div>
    <div class="col-md-12">
        <table class="w-100 table-borderless">
            <thead class="thead-dark">
                <tr>
                    <th class="w-25">Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
            @if(isset($log['request']))
                @foreach($log['request'] as $key => $value)
                    <tr>
                        <td class="font-weight-bold">{{ $key }}</td>

                        @if (is_array($value) || is_object($value))
                            <td>{!! "<pre>". e(print_r($value, true)) ."</pre>" !!}</td>
                        @else
                            <td>{{ $value }}</td>
                        @endif
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>

<div class="row pt-5">
    <div class="col-md-12">
        <h5 class="text-uppercase">Response</h5>
        <hr>
    </div>
    <div class="col-md-12">
        <table class="w-100 table-borderless">
            <thead class="thead-dark">
                <tr>
                    <th class="w-25">Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
            @if(isset($log['response']))
                @foreach($log['response'] as $name => $value)
                    <tr>
                        <td class="font-weight-bold">{{ $name }}</td>

                        @if (is_array($value) || is_object($value))
                            <td>{!! "<pre>". e(print_r($value, true)) ."</pre>" !!}</td>
                        @else
                            <td>{{ $value }}</td>
                        @endif
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>

@if(isset($log['query']))
<div class="row pt-5">
    <div class="col-md-12">
        <h5 class="text-uppercase">Query</h5>
        <hr>
    </div>
    <div class="col-md-12">
        <table class="w-100 table-borderless">
            <thead class="thead-dark">
                <tr>
                    <th>Execute</th>
                    <th style="width: 100px;">Time (ms)</th>
                </tr>
            </thead>
            <tbody>
            @foreach($log['query'] as $key => $value)
                <tr>
                    <td>{{ $value['query'] }}</td>
                    <td class="text-right">{{ $value['time'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
