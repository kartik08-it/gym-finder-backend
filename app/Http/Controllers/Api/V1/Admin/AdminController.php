<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\GymStatus;
use App\Events\GymApproved;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\GymResource;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\Gym;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return $this->ok([
            'stats' => [
                'total_users' => User::count(),
                'total_owners' => User::where('role', 'gym_owner')->count(),
                'total_gyms' => Gym::count(),
                'pending_gyms' => Gym::where('status', GymStatus::PENDING->value)->count(),
                'total_bookings' => Booking::count(),
                'revenue' => (float) Payment::where('status', 'captured')->sum('amount'),
            ],
            'recent_gyms' => GymResource::collection(Gym::latest()->take(5)->get()),
            'recent_users' => UserResource::collection(User::latest()->take(5)->get()),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $q = User::query();
        if ($role = $request->query('role')) $q->where('role', $role);
        if ($search = $request->query('q')) {
            $q->where(fn ($x) => $x->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"));
        }
        return $this->ok(UserResource::collection($q->latest()->paginate(15)));
    }

    public function updateUserStatus(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:active,suspended,pending']);
        $user->update($data);
        return $this->ok(new UserResource($user));
    }

    public function pendingGyms(): JsonResponse
    {
        return $this->ok(GymResource::collection(
            Gym::with(['city', 'owner'])->where('status', GymStatus::PENDING->value)->paginate(15)
        ));
    }

    public function approveGym(Gym $gym): JsonResponse
    {
        $gym->update([
            'status' => GymStatus::APPROVED->value,
            'is_verified' => true,
            'approved_at' => now(),
        ]);
        event(new GymApproved($gym));
        return $this->ok(new GymResource($gym), 'Gym approved.');
    }

    public function rejectGym(Request $request, Gym $gym): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $gym->update([
            'status' => GymStatus::REJECTED->value,
            'rejection_reason' => $data['reason'],
        ]);
        return $this->ok(new GymResource($gym), 'Gym rejected.');
    }

    public function suspendGym(Gym $gym): JsonResponse
    {
        $gym->update(['status' => GymStatus::SUSPENDED->value]);
        return $this->ok(new GymResource($gym), 'Gym suspended.');
    }

    public function revenue(Request $request): JsonResponse
    {
        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = Payment::where('status', 'captured')
            ->whereBetween('captured_at', [$from, $to])
            ->selectRaw('DATE(captured_at) as day, SUM(amount) as total')
            ->groupBy('day')->orderBy('day')->get();

        return $this->ok([
            'from' => $from, 'to' => $to,
            'series' => $rows,
            'total' => (float) $rows->sum('total'),
        ]);
    }
}
