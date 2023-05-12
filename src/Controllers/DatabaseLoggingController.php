<?php

namespace AdityaDarma\LaravelDatabaseLogging\Controllers;

use AdityaDarma\LaravelDatabaseLogging\Models\DatabaseLogging;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatabaseLoggingController extends Controller
{
    public function index(Request $request)
    {
        return DatabaseLogging::with(['loggable'])->paginate(1);
    }
}
