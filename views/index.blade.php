
<!doctype html>
<html lang="en">
    <head>
        <title>Database Logging</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' type='text/css'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css"/>
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
                    <form id="form-search" data-url="{{ config('database-logging.route_path') }}/datatable">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="user">User</label>
                                <select class="form-control" name="user" id="user">
                                    <option value="">- All -</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->loggable_type }}|{{ $user->loggable_id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date-start">Start Date</label>
                                <input type="date" class="form-control" id="start-date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date-end">End Date</label>
                                <input type="date" class="form-control" id="end-date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2 pt-4">
                                <a href="javascript:void(0)" class="btn btn-primary mt-2" id="search"><i class="fas fa-search"></i></a>
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
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $('[data-toggle="tooltip"]').tooltip();

            $(document).ready(function () {
                var table = $('#accordion').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: false,
                    ordering: false,
                    pageLength: 10,
                    ajax: {
                        url: $('#form-search').data('url'),
                        data: function(d) {
                            d.user = $('#user').val();
                            d.start_date = $('#start-date').val();
                            d.end_date = $('#end-date').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                        { data: 'user', name: 'user' },
                        { data: 'content', name: 'content' },
                    ],
                });

                $('#search').on('click', function () {
                    table.cleanData;
                    table.ajax.reload(null, true);
                });
            })
        </script>
    </body>
</html>

