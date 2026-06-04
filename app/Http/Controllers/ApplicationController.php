<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Application;
use App\Models\Vacancy;
use App\Services\GeminiAIService;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'industry') {
            // Get applications for this industry's vacancies
            $vacanciesId = $request->user()->vacancies()->pluck('id');
            $applications = Application::whereIn('vacancy_id', $vacanciesId)->with(['user.profile', 'user.skills', 'user.portfolios', 'vacancy'])->get();
            return response()->json($applications);
        }

        if ($request->user()->role === 'student') {
            // Get applications for this student
            $applications = $request->user()->applications()->with('vacancy.user.profile')->get();
            return response()->json($applications);
        }

        return response()->json([]);
    }

    public function store(Request $request, GeminiAIService $aiService)
    {
        if ($request->user()->role !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'vacancy_id' => 'required|exists:vacancies,id',
            'cover_letter' => 'nullable|string',
        ]);

        $vacancy = Vacancy::findOrFail($validated['vacancy_id']);
        
        if ($vacancy->status !== 'open') {
            return response()->json(['message' => 'Vacancy is closed'], 422);
        }

        if ($vacancy->deadline && now()->startOfDay()->gt(\Carbon\Carbon::parse($vacancy->deadline)->startOfDay())) {
            return response()->json(['message' => 'Vacancy deadline has passed'], 422);
        }

        if ($request->user()->applications()->where('vacancy_id', $vacancy->id)->exists()) {
            return response()->json(['message' => 'You have already applied to this vacancy'], 422);
        }
        // Prepare skills data
        $studentSkills = $request->user()->skills()->where('is_validated', true)->get()->map(function($s) {
            return $s->name . ' (' . $s->proficiency . ')';
        })->toArray();
        
        $requiredSkills = $vacancy->required_skills ?? [];

        // Call Gemini AI
        $aiResult = $aiService->calculateMatchRate($studentSkills, $requiredSkills);

        $application = $request->user()->applications()->create([
            'vacancy_id' => $validated['vacancy_id'],
            'cover_letter' => $validated['cover_letter'] ?? null,
            'match_rate' => $aiResult['match_rate'],
            'ai_analysis' => $aiResult['analysis'],
            'status' => 'Pending Review',
        ]);

        // Send Notification to Industry
        if ($vacancy->user_id) {
            \App\Models\Notification::create([
                'user_id' => $vacancy->user_id,
                'title' => 'Lamaran Magang Baru',
                'message' => 'Siswa ' . $request->user()->name . ' telah melamar untuk posisi ' . $vacancy->title . '.',
                'type' => 'application_status'
            ]);
        }

        return response()->json($application, 201);
    }

    public function update(Request $request, Application $application)
    {
        if ($request->user()->role !== 'industry') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify the application belongs to a vacancy owned by this industry
        if ($application->vacancy->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Pending Review,Reviewed,Accepted,Rejected',
        ]);

        $application->update($validated);

        // Send Notification to Student
        if ($application->user_id) {
            \App\Models\Notification::create([
                'user_id' => $application->user_id,
                'title' => 'Status Lamaran Diperbarui',
                'message' => 'Lamaran magang Anda untuk posisi ' . $application->vacancy->title . ' telah diperbarui menjadi: ' . $validated['status'] . '.',
                'type' => 'application_status'
            ]);
        }

        return response()->json($application);
    }

    public function destroy(Request $request, Application $application)
    {
        if ($request->user()->role !== 'student' || $application->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application->delete();

        return response()->json(null, 204);
    }
}
