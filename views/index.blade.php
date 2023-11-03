
<!doctype html>
<html lang="en">
<head>
    <title>Database Logging</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{ asset(config('database-logging.assets_path').'/css/style.css') }}">

</head>
<body>
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mb-4">
                <h2 class="heading-section">Database Logging</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-wrap">
                    <table class="table myaccordion table-hover" id="accordion">
                        <thead>
                            <tr>
                                <th width="20px">#</th>
                                <th>User Name</th>
                                <th>IP Address</th>
                                <th>URL</th>
                                <th width="100px">Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $key => $log)
                                <tr data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="true" aria-controls="collapse{{$key}}">
                                    <th scope="row">{{ $key+1 }}</th>
                                    <td>{{ $log->name }}</td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>{{ $log->url }}</td>
                                    <td>{{ $log->method }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" id="collapse{{$key}}" class="collapse acc" data-parent="#accordion">
                                        <b>Data</b>:
                                        <pre>
                                            {{ print_r($log->data, true) }}
                                        </pre>
                                        <b>Request</b>:
                                        <pre>
                                            {{ print_r($log->request, true) }}
                                        </pre>
                                        <b>Response</b>:
                                        <pre>
                                            {{ print_r($log->response, true) }}
                                        </pre>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-left">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset(config('database-logging.assets_path').'/js/jquery.min.js') }}"></script>
<script src="{{ asset(config('database-logging.assets_path').'/js/popper.js') }}"></script>
<script src="{{ asset(config('database-logging.assets_path').'/js/bootstrap.min.js') }}"></script>
<script src="{{ asset(config('database-logging.assets_path').'/js/main.js') }}"></script>

</body>
</html>

