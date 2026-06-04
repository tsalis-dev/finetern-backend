<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Services\GeminiAIService;
use App\Models\Vacancy;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();
        
        // Update user name if provided
        if ($request->has('name')) {
            $user->update(['name' => $request->name]);
        }

        $profileData = $request->only([
            'phone', 'address', 'school', 'department', 'nisn', 
            'gender', 'birth_date', 'bio', 'npsn', 'nib', 
            'company_name', 'company_description'
        ]);

        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('cvs', 'public');
            $profileData['cv_path'] = $path;
        }

        // Update profile
        $user->profile()->update($profileData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load('profile')
        ]);
    }

    public function students(Request $request)
    {
        if (!in_array($request->user()->role, ['school', 'industry'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // For school or industry to view students
        $query = User::with(['profile', 'skills', 'portfolios', 'applications.vacancy.user.profile'])->where('role', 'student');

        if ($request->has('school')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('school', $request->school);
            });
        }

        if ($request->has('skill')) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->skill . '%');
            });
        }

        return response()->json($query->get());
    }

    public function schools()
    {
        $schools = User::with('profile')->where('role', 'school')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'npsn' => $user->profile->npsn ?? '',
                'school' => $user->profile->school ?? $user->name,
            ];
        })->values();
        return response()->json($schools);
    }

    public function skillGap(Request $request, GeminiAIService $aiService)
    {
        if ($request->user()->role !== 'school') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schoolName = $request->user()->profile->school ?? $request->user()->name;

        // Get all validated skills from students of this school
        $students = User::with('skills')->where('role', 'student')->whereHas('profile', function($q) use ($schoolName) {
            $q->where('school', $schoolName);
        })->get();

        $studentsSkills = [];
        foreach ($students as $student) {
            foreach ($student->skills as $skill) {
                if ($skill->is_validated) {
                    $studentsSkills[] = $skill->name . ' (' . $skill->proficiency . ')';
                }
            }
        }
        // Take unique ones or just count them for AI
        $studentsSkills = array_values(array_unique($studentsSkills));

        // Get industry demands (required skills from open vacancies)
        $vacancies = Vacancy::where('status', 'open')->get();
        $industryDemands = [];
        foreach ($vacancies as $vacancy) {
            $reqs = $vacancy->required_skills;
            if (is_string($reqs)) {
                $reqs = json_decode($reqs, true);
            }
            if (is_array($reqs)) {
                $industryDemands = array_merge($industryDemands, $reqs);
            }
        }
        $industryDemands = array_values(array_unique($industryDemands));

        $result = $aiService->analyzeSkillGap($schoolName, $studentsSkills, $industryDemands);

        return response()->json($result);
    }
}
