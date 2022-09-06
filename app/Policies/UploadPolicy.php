<?php

namespace App\Policies;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Upload $upload)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Upload $upload)
    {
        //
    }

    public function delete(User $user, Upload $upload)
    {
        //
    }

    public function restore(User $user, Upload $upload)
    {
        //
    }

    public function forceDelete(User $user, Upload $upload)
    {
        //
    }
}
