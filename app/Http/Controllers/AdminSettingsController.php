<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.settings.index', [
            'appName' => config('app.name'),
            'appEnv' => config('app.env'),
            'dbConnection' => config('database.default'),
            'dbDatabase' => config('database.connections.'.config('database.default').'.database'),
            'adminEmail' => config('admin.email'),
            'adminPasswordConfigured' => filled(config('admin.password')),
            'updatedAt' => now()->format('d M Y, H:i'),
        ]);
    }
}
