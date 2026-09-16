<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminUserRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()->whereIn('role', ['customer', 'owner', 'cleaner']);

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findOrFail(int $id): User
    {
        return User::query()
            ->whereIn('role', ['customer', 'owner', 'cleaner'])
            ->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'role' => $data['role'],
            'password' => $data['password'],
            'status' => 'active',
        ]);
    }

    public function update(User $user, array $data): User
    {
        $user->fill($data)->save();
        return $user->refresh();
    }

    public function setStatus(User $user, string $status): User
    {
        $user->forceFill(['status' => $status])->save();
        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}