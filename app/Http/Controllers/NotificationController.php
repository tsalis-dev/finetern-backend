<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = \App\Models\Notification::where('user_id', $request->user()->id)
            ->latest()
            ->take(50)
            ->get();
        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = \App\Models\Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        \App\Models\Notification::where('user_id', $request->user()->id)->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }
}
