<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlaylistPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {
        //
    }

    public function view(User $user, Playlist $playlist): bool
    {
        //
    }

    public function create(User $user): bool
    {
        //
    }

    public function update(User $user, Playlist $playlist): bool
    {
        //
    }

    public function delete(User $user, Playlist $playlist): bool
    {
        //
    }

    public function restore(User $user, Playlist $playlist): bool
    {
        //
    }

    public function forceDelete(User $user, Playlist $playlist): bool
    {
        //
    }
}
