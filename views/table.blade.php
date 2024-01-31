<div class="row">
    <div class="col-md-12">
        <h5 class="text-uppercase">Data</h5>
        <hr>
    </div>
    @foreach($log->dataObject as $data)
        <div class="col-md-12">
            <div class="card mb-3">
                <table class="table-borderless w-100" style="background-color: #454d55; border-color: #454d55; color: #ffffff">
                    <tbody>
                        <tr>
                            <td class="fs-4"><b>Table :</b> {{ $data->table }}</td>
                        </tr>
                        <tr>
                            <td><b>Event :</b> {{ $data->event }}</td>
                        </tr>
                        <tr>
                            <td><b>ID :</b> {{ $data->id }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="w-100">
                    <thead class="thead-dark">
                        <tr>
                            <th>Column</th>
                            <th style="width: 35%">Old</th>
                            <th style="width: 35%">New</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($data->data)
                        @foreach($data->data as $column)
                            <tr>
                                <td>{{ $column->column }}</td>
                                <td>{{ $column->old }}</td>
                                <td>{{ $column->new }}</td>
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
            @if($log->requestObject)
                @foreach($log->requestObject as $key => $value)
                    <tr>
                        <td class="font-weight-bold">{{ $key }}</td>
                        <td colspan="3">{!! is_array($value) || is_object($value) ? "<pre>". print_r($value, true) ."</pre>" : $value !!}</td>
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
            @if($log->responseObject)
                @foreach($log->responseObject as $name => $value)
                    <tr>
                        <td class="font-weight-bold">{{ $name }}</td>
                        <td colspan="3">{!! is_array($value) || is_object($value) ? "<pre>". print_r($value, true) ."</pre>" : $value !!}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>

@if($log->query)
<div class="row pt-5">
    <div class="col-md-12">
        <h5 class="text-uppercase">Query</h5>
        <hr>
    </div>
    <div class="col-md-12">
        <table class="w-100 table-borderless">
            <thead class="thead-dark">
            <tr>
                <th class="w-75">Execute</th>
                <th>Time (second)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($log->queryObject as $key => $value)
                <tr>
                    <td>{{ $value->query }}</td>
                    <td>{{ $value->time }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif