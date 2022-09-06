<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
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

    public function view(User $user, Purchase $purchase): bool
    {
        //
    }

    public function create(User $user): bool
    {
        //
    }

    public function update(User $user, Purchase $purchase): bool
    {
        //
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        //
    }

    public function restore(User $user, Purchase $purchase): bool
    {
        //
    }

    public function forceDelete(User $user, Purchase $purchase): bool
    {
        //
    }
}
