<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $promotions = $query->latest()->paginate(15);

        return view('commercial.promotions.index', compact('promotions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_amount'] = $validated['min_amount'] ?? 0;

        $promotion = Promotion::create($validated);

        ActivityLogger::log('created_promotion', 'Création du code promo : '.$promotion->code, $promotion);

        return back()->with('success', 'Code promo créé avec succès.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code,'.$promotion->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $promotion->update($validated);

        ActivityLogger::log('updated_promotion', 'Mise à jour du code promo : '.$promotion->code, $promotion);

        return back()->with('success', 'Code promo mis à jour avec succès.');
    }

    public function destroy(Promotion $promotion)
    {
        $code = $promotion->code;
        $promotion->delete();

        ActivityLogger::log('deleted_promotion', 'Suppression du code promo : '.$code, null);

        return back()->with('success', 'Code promo supprimé avec succès.');
    }

    public function toggle(Promotion $promotion)
    {
        $promotion->is_active = ! $promotion->is_active;
        $promotion->save();

        return back()->with('success', 'Statut du code promo modifié.');
    }
}
