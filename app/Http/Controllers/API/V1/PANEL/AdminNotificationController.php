<?php

namespace App\Http\Controllers\API\V1\PANEL;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class AdminNotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Notification::latest()
                ->take(20)
                ->get()
        ]);
    }

    public function unread()
    {
        return response()->json([
            'success' => true,
            'count' => Notification::where(
                'is_read',
                false
            )->count()
        ]);
    }

    public function markRead($id)
    {
        Notification::where(
            'id',
            $id
        )->update([
            'is_read' => true
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}