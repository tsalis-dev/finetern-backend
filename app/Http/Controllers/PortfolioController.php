<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Portfolio;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->portfolios);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'category' => 'nullable|string',
            'tech_stack' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $portfolio = $request->user()->portfolios()->create($validated);

        return response()->json($portfolio, 201);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        if ($request->user()->role === 'industry') {
            // Industry verifying a portfolio
            $portfolio->update([
                'status' => $request->status ?? 'verified',
                'verifier_name' => $request->user()->name,
            ]);
            return response()->json($portfolio);
        }

        // Student updating their own portfolio
        if ($portfolio->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $portfolio->update($request->only(['title', 'url', 'category', 'tech_stack', 'description']));

        return response()->json($portfolio);
    }

    public function destroy(Request $request, Portfolio $portfolio)
    {
        if ($portfolio->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $portfolio->delete();

        return response()->json(null, 204);
    }
}
