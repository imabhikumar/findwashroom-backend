<?php

namespace App\Repositories;

use App\Models\Badge;
use App\Models\PropertyBadge;
use App\Models\TrustEvent;
use App\Models\UserBadge;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TrustRepository
{
    public function events(array $filters): LengthAwarePaginator
    {
        return TrustEvent::query()
            ->with('user')
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['event_type'] ?? null, fn ($q, $v) => $q->where('event_type', $v))
            ->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('created_at')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function users(array $filters): LengthAwarePaginator
    {
        $query = DB::table('users')
            ->leftJoin('trust_events', 'trust_events.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->whereIn('users.role', ['customer', 'owner', 'cleaner'])
            ->when($filters['role'] ?? null, fn ($q, $v) => $q->where('users.role', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s->where('users.name', 'like', "%{$v}%")->orWhere('users.email', 'like', "%{$v}%")->orWhere('users.mobile', 'like', "%{$v}%")))
            ->select('users.id', 'users.name', 'users.email', 'users.mobile', 'users.role')
            ->selectRaw('COALESCE(SUM(trust_events.score_change), 0) as trust_score')
            ->selectRaw('MAX(trust_events.created_at) as latest_event_at')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.mobile', 'users.role');

        if (isset($filters['min_score'])) $query->having('trust_score', '>=', $filters['min_score']);
        if (isset($filters['max_score'])) $query->having('trust_score', '<=', $filters['max_score']);

        return $query->orderByDesc('trust_score')->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function user(int $id): array
    {
        $user = DB::table('users')->where('id', $id)->first(['id', 'name', 'email', 'mobile', 'role']);
        abort_if(! $user, 404, 'User not found.');

        return [
            'user' => (array) $user,
            'trust_score' => (int) TrustEvent::where('user_id', $id)->sum('score_change'),
            'trust_events' => TrustEvent::where('user_id', $id)->orderBy('created_at')->get()->all(),
            'badges' => DB::table('user_badges')->join('badges', 'badges.id', '=', 'user_badges.badge_id')->where('user_badges.user_id', $id)->get(['badges.id', 'badges.name', 'badges.description', 'badges.type as badge_type', 'user_badges.awarded_at'])->all(),
        ];
    }

    public function createEvent(array $data): TrustEvent
    {
        $category = $data['score_change'] > 0
            ? 'positive'
            : ($data['score_change'] < 0 ? 'negative' : 'neutral');

        return TrustEvent::create($data + [
            'event_category' => $category,
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function badges()
    {
        return Badge::query()->latest('id')->get()->map(function ($badge) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'badge_type' => $badge->type,
                'created_at' => $badge->created_at,
            ];
        });
    }

    public function createBadge(array $data): Badge
    {
        return Badge::create([
            'name' => $data['name'],
            'slug' => str()->slug($data['name']) . '-' . str()->random(5),
            'description' => $data['description'] ?? null,
            'type' => $data['badge_type'],
            'criteria' => [],
            'is_auto_assign' => false,
            'is_active' => true,
        ]);
    }

    public function findBadge(int $id): Badge { return Badge::findOrFail($id); }

    public function updateBadge(Badge $badge, array $data): Badge
    {
        $badge->fill(['name' => $data['name'], 'description' => $data['description'] ?? null, 'type' => $data['badge_type']])->save();
        return $badge->refresh();
    }

    public function assignBadge(array $data, ?int $adminId): array
    {
        if (isset($data['user_id'])) {
            $row = UserBadge::firstOrCreate(['user_id' => $data['user_id'], 'badge_id' => $data['badge_id']], ['awarded_at' => now(), 'awarded_by' => $adminId]);
            return ['id' => $row->id, 'user_id' => $row->user_id, 'badge_id' => $row->badge_id, 'target' => 'user'];
        }

        $row = PropertyBadge::firstOrCreate(['property_id' => $data['property_id'], 'badge_id' => $data['badge_id']], ['awarded_at' => now(), 'awarded_by' => $adminId]);
        return ['id' => $row->id, 'property_id' => $row->property_id, 'badge_id' => $row->badge_id, 'target' => 'property'];
    }

    public function revokeBadge(array $data): array
    {
        $query = isset($data['user_id']) ? UserBadge::where($data) : PropertyBadge::where($data);
        $row = $query->firstOrFail();
        $result = $row->toArray();
        $row->delete();
        return $result;
    }
}