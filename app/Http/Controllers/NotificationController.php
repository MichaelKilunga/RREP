<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\NotificationTemplate;

class NotificationController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::all();
        $logs = CommunicationLog::with('customer')->latest()->take(50)->get();

        return view('notifications.index', compact('templates', 'logs'));
    }
}
