<?php

namespace AdityaDarma\LaravelDatabaseLogging\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use App\Http\Controllers\Controller;

class DatabaseLoggingController extends Controller
{
    public function index()
    {
        $data['logs'] =  DatabaseLogging::with(['loggable'])->latest()->paginate(10);

        return view('LaravelDatabaseLogging::index', $data);
    }
}
