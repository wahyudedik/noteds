<?php

namespace App\Policies;

use App\Models\AnalyticsEvent;
use App\Models\User;

class AnalyticsEventPolicy
{
    public function viewAny(?User $user): bool
    {
        if (!$user) return false;
        return $user->isAdmin() || $user->hasRole('analyst');
    }

    public function view(?User $user, AnalyticsEvent $event): bool
    {
        if (!$user) return false;
        return $user->isAdmin() || $user->hasRole('analyst') || $user->hasRole('viewer');
    }

    public function create(?User $user): bool
    {
        if (!$user) return false;
        return $user->isAdmin() || $user->hasRole('analyst');
    }

    public function update(?User $user, AnalyticsEvent $event): bool
    {
        if (!$user) return false;
        return $user->isAdmin();
    }

    public function delete(?User $user, AnalyticsEvent $event): bool
    {
        if (!$user) return false;
        return $user->isAdmin();
    }

    public function export(?User $user): bool
    {
        if (!$user) return false;
        return $user->isAdmin() || $user->hasRole('analyst');
    }
}
