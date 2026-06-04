<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Skill;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->skills);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'proficiency' => 'required|string',
        ]);

        $skill = $request->user()->skills()->create($validated);

        return response()->json($skill, 201);
    }

    public function update(Request $request, Skill $skill)
    {
        if ($request->user()->role === 'school') {
            // School validating a skill
            $skill->update([
                'is_validated' => $request->is_validated ?? true,
                'validator_name' => $request->user()->name,
            ]);
            return response()->json($skill);
        }

        // Student updating their own skill
        if ($skill->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $skill->update($request->only(['proficiency']));

        return response()->json($skill);
    }

    public function destroy(Request $request, Skill $skill)
    {
        if ($skill->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $skill->delete();

        return response()->json(null, 204);
    }
}
