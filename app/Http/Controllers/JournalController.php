<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id'
        ]);

        $application = \App\Models\Application::with('vacancy')->findOrFail($request->application_id);

        if ($request->user()->role === 'student' && $application->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($request->user()->role === 'industry' && $application->vacancy->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $journals = \App\Models\Journal::where('application_id', $request->application_id)
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json($journals);
    }

    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'date' => 'required|date',
            'activity' => 'required|string',
            'hours' => 'required|integer|min:1|max:24',
        ]);

        $application = \App\Models\Application::with('vacancy.user')->findOrFail($request->application_id);
        
        if ($application->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Create Journal
        $journal = \App\Models\Journal::create([
            'application_id' => $request->application_id,
            'date' => $request->date,
            'activity' => $request->activity,
            'hours' => $request->hours,
            'status' => 'Pending'
        ]);

        // Send Notification to Industry
        if ($application && $application->vacancy && $application->vacancy->user_id) {
            \App\Models\Notification::create([
                'user_id' => $application->vacancy->user_id,
                'title' => 'Jurnal Magang Baru',
                'message' => 'Siswa ' . $request->user()->name . ' telah menambahkan jurnal magang baru.',
                'type' => 'journal_update'
            ]);
        }

        return response()->json($journal, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected'
        ]);

        $journal = \App\Models\Journal::findOrFail($id);
        $application = \App\Models\Application::with('vacancy', 'user')->find($journal->application_id);

        if (!$application || $application->vacancy->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $journal->update(['status' => $request->status]);

        // Send Notification to Student
        if ($application && $application->user_id) {
            \App\Models\Notification::create([
                'user_id' => $application->user_id,
                'title' => 'Status Jurnal Diperbarui',
                'message' => 'Jurnal magang Anda pada ' . $journal->date . ' telah di ' . strtolower($request->status) . ' oleh industri.',
                'type' => 'journal_update'
            ]);
        }

        return response()->json($journal);
    }
}
