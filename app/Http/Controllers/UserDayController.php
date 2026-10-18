<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserDailyActivityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tableau de bord journalier : « Ma journée » pour chacun, et la journée de chaque utilisateur pour l'administrateur.
 */
class UserDayController extends Controller
{
    public function mine(Request $request, UserDailyActivityService $activity): View
    {
        return $this->render($request, $request->user(), $activity, true);
    }

    public function show(Request $request, User $user, UserDailyActivityService $activity): View
    {
        return $this->render($request, $user, $activity, $user->is($request->user()));
    }

    private function render(Request $request, User $user, UserDailyActivityService $activity, bool $isSelf): View
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today']]);
        $date = $request->filled('date') ? CarbonImmutable::parse($request->input('date')) : CarbonImmutable::today();

        return view('day.show', [
            'user' => $user->loadMissing('roles', 'employee'),
            'date' => $date,
            'isSelf' => $isSelf,
            'day' => $activity->forDay($user, $date),
            'dayRoute' => fn (CarbonImmutable $target) => $isSelf
                ? route('my-day', ['date' => $target->toDateString()])
                : route('admin.users.day', ['user' => $user, 'date' => $target->toDateString()]),
        ]);
    }
}
