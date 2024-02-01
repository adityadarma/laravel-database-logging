
<!doctype html>
<html lang="en">
<head>
    <title>Database Logging</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="{{ asset(config('database-logging.assets_path').'/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset(config('database-logging.assets_path').'/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset(config('database-logging.assets_path').'/css/style.css') }}">

</head>
<body>
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mb-4 pt-3">
                <h2 class="heading-section">Database Logging</h2>
            </div>
        </div>
        <div class="mb-4">
            <form action="{{ config('database-logging.route_path') }}" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <label for="user">User</label>
                        <select class="form-control" name="user" id="user">
                            <option value="">- All -</option>
                            @foreach($users as $user)
                                <option value="{{ $user->loggable_type }}|{{ $user->loggable_id }}" {{ request()->user === $user->loggable_type."|".$user->loggable_id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="table">Table</label>
                        <select class="form-control" name="table" id="table">
                            <option value="">- All -</option>
                            @foreach($tables as $table)
                                <option value="{{ $table }}" {{ request()->table == $table ? 'selected' : '' }}>{{ $table }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="id">ID</label>
                        <input type="text" class="form-control" name="id" id="id" value="{{ request()->id }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date-start">Start Date</label>
                        <input type="date" class="form-control" name="date_start" id="date-start" value="{{ request()->date_start }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date-end">End Date</label>
                        <input type="date" class="form-control" name="date_end" id="date-end" value="{{ request()->date_end }}">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary mt-2"><i class="fa fa-search"></i></button>
                        <a href="{{ config('database-logging.route_path') }}" class="btn btn-primary mt-2"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-wrap table-responsive">
                    <table class="table myaccordion table-hover table-striped table-bordered" id="accordion">
                        <thead>
                            <tr>
                                <th width="20px">#</th>
                                <th>User Name</th>
                                <th>Client</th>
                                <th width="100px">Method</th>
                                <th width="200px">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $key => $log)
                                <tr data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="true" aria-controls="collapse{{$key}}">
                                    <th scope="row">{{ $logs->firstItem() + $key }}</th>
                                    <td>{{ $log->name }}</td>
                                    <td>
                                        <b>IP:</b> {{ $log->ip_address }}<br>
                                        <b>Agent:</b> {{ $log->agent }}<br>
                                        <b>Host:</b> {{ $log->host }}<br>
                                        <b>Path:</b> {{ $log->path }}<br>
                                    </td>
                                    <td>{{ $log->method }}</td>
                                    <td>{{ $log->date_create }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" id="collapse{{$key}}" class="collapse acc" data-parent="#accordion">
                                        @include('LaravelDatabaseLogging::table', ['log', $log])
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

