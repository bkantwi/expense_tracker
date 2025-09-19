<?php

namespace App\Policies;

use App\Models\Recurrence;
use App\Models\User;

class RecurrencePolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Recurrence $r): bool
    {
        return $user->id === $r->user_id;
    }

    public function create(User $user): bool { return true; }

    public function update(User $user, Recurrence $r): bool
    {
        return $user->id === $r->user_id;
    }

    public function delete(User $user, Recurrence $r): bool
    {
        return $user->id === $r->user_id;
    }
}
