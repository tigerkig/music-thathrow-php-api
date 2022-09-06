<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterRequest;
use App\Http\Resources\ProducerResource;
use App\Models\Beat;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class ProducerController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function trending(Request $request)
    {
        $beats = Beat::withCount('purchases')
            ->orderBy('purchases_count', 'desc')
            ->join('beat_purchase', function ($join) {
                $join->on('beats.id', '=', 'beat_purchase.beat_id')
                    ->whereNull('beat_purchase.deleted_at')
                    ->where('beat_purchase.created_at', '>=', now()->subDays(30));
            })->get();

        $users = User::query()->
            whereIn('id', $beats->pluck('user_id')->toArray())
            ->limit(12)
            ->get();

        return ProducerResource::collection($users);
    }
}
