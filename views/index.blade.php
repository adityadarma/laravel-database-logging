
<!doctype html>
<html lang="en">
    <head>
        <title>Database Logging</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css" integrity="sha512-q3eWabyZPc1XTCmF+8/LuE1ozpg5xxn7iO89yfSOd5/oKvyqLngoNGsx8jq92Y8eXJ/IRxQbEC+FGSYxtk2oiw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.min.css">
        <style>
            .container {
                max-width: 98vw;
            }

            .table th {
                font-size: 15px;
            }

            .table td {
                font-size: 13px;
            }

            .table td,
            .table th {
                word-wrap: break-word;
                word-break: break-all;
            }
        </style>
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
                                <label for="date-start">Start Date</label>
                                <input type="date" class="form-control" name="date_start" id="date-start" value="{{ request()->date_start }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date-end">End Date</label>
                                <input type="date" class="form-control" name="date_end" id="date-end" value="{{ request()->date_end }}">
                            </div>
                            <div class="col-md-2 pt-4">
                                <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-search"></i></button>
                                <a href="{{ config('database-logging.route_path') }}" class="btn btn-primary mt-2"><i class="fas fa-sync"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-wrap">
                            <table class="table myaccordion table-hover table-striped table-bordered" id="accordion">
                                <thead>
                                    <tr>
                                        <th width="50px">#</th>
                                        <th width="200px">User Name</th>
                                        <th>Content</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($logs as $key => $log)
                                    <tr data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="true" aria-controls="collapse{{$key}}">
                                        <th scope="row">{{ $key+1 }}</th>
                                        <td>{{ $log['name'] }}</td>
                                        <td>
                                            <b>Date:</b> {{ $log['date_created'] }}<br>
                                            <b>IP:</b> {{ $log['ip_address'] }}<br>
                                            <b>Agent:</b> {{ $log['agent'] }}<br>
                                            <b>Host:</b> {{ $log['host'] }}<br>
                                            <b>Path:</b> {{ $log['path'] }}<br>
                                            <b>Method:</b> {{ $log['method'] }}<br>
                                            <table>
                                                <td colspan="5" id="collapse{{$key}}" class="collapse acc" data-parent="#accordion">
                                                    @include('LaravelDatabaseLogging::table', ['log', $log])
                                                </td>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $('[data-toggle="tooltip"]').tooltip()
            $('#accordion').DataTable();
        </script>
    </body>
</html>

