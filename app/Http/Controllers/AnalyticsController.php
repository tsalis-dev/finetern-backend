<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'school') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schoolName = $request->user()->profile->school ?? $request->user()->name;

        // Get students from this school
        $students = User::where('role', 'student')->whereHas('profile', function($q) use ($schoolName) {
            $q->where('school', $schoolName);
        })->get();

        $studentIds = $students->pluck('id');

        $totalStudents = $students->count();
        $totalApplications = Application::whereIn('user_id', $studentIds)->count();
        $acceptedApplications = Application::whereIn('user_id', $studentIds)->where('status', 'Accepted')->count();
        
        $activeIndustriesCount = Vacancy::whereHas('applications', function($q) use ($studentIds) {
            $q->whereIn('user_id', $studentIds);
        })->distinct('user_id')->count('user_id');

        $recentApplications = Application::with(['user', 'vacancy.user.profile'])
            ->whereIn('user_id', $studentIds)
            ->latest()
            ->take(5)
            ->get();

        $departments = \App\Models\Profile::whereIn('user_id', $studentIds)
            ->select('department', \DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get();

        return response()->json([
            'total_students' => $totalStudents,
            'total_applications' => $totalApplications,
            'accepted_applications' => $acceptedApplications,
            'active_industries' => $activeIndustriesCount,
            'placement_rate' => $totalStudents > 0 ? ($acceptedApplications / $totalStudents) * 100 : 0,
            'recent_activities' => $recentApplications,
            'departments' => $departments
        ]);
    }
}
