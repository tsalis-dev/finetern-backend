<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Vacancy;

class VacancyController extends Controller
{
    public function index(Request $request)
    {
        // If industry, return their own. If student/school, return all open vacancies.
        if ($request->user()->role === 'industry') {
            return response()->json($request->user()->vacancies);
        }

        $vacancies = Vacancy::where('status', 'open')->with('user.profile')->get();

        if ($request->user()->role === 'student') {
            $studentSkills = $request->user()->skills->pluck('name')->map(fn($s) => strtolower(trim($s)))->toArray();
            
            $vacancies->transform(function ($vacancy) use ($studentSkills) {
                $requiredSkills = array_map(fn($s) => strtolower(trim($s)), $vacancy->required_skills ?? []);
                
                if (count($requiredSkills) > 0) {
                    $matched = array_intersect($requiredSkills, $studentSkills);
                    $matchRate = (int) round((count($matched) / count($requiredSkills)) * 100);
                } else {
                    $matchRate = 100;
                }
                
                $vacancy->match_rate = $matchRate;
                return $vacancy;
            });
        }

        return response()->json($vacancies);
    }

    public function show(Request $request, Vacancy $vacancy)
    {
        return response()->json($vacancy->load('user.profile'));
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'industry') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'work_mode' => 'nullable|string',
            'quota' => 'nullable|integer',
            'deadline' => 'nullable|date',
            'required_skills' => 'nullable|array',
            'category' => 'nullable|string',
        ]);

        $vacancy = $request->user()->vacancies()->create($validated);

        return response()->json($vacancy, 201);
    }

    public function update(Request $request, Vacancy $vacancy)
    {
        if ($vacancy->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'work_mode' => 'nullable|string',
            'quota' => 'nullable|integer',
            'deadline' => 'nullable|date',
            'required_skills' => 'nullable|array',
            'category' => 'nullable|string',
            'status' => 'sometimes|string|in:open,closed',
        ]);

        $vacancy->update($validated);

        return response()->json($vacancy);
    }

    public function destroy(Request $request, Vacancy $vacancy)
    {
        if ($vacancy->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vacancy->delete();

        return response()->json(null, 204);
    }
}
