<?php

namespace App\Http\Controllers;

use App\Http\Resources\BeatResource;
use App\Http\Resources\ProducerResource;
use App\Http\Resources\UserResource;
use App\Models\Beat;
use App\Models\User;
use Illuminate\Http\Request;
//use JeroenG\Explorer\Domain\Syntax\Range;

class SearchController extends Controller
{
    public function producers(Request $request)
    {
        $users = User::search($request->input('query'))
            ->get();
        return ProducerResource::collection($users);
    }

    public function beats(Request $request)
    {
        $beats = Beat::search($request->input('query'))->get();
        $beatsArray = [];
        foreach ($beats as $item) {
            $beat = Beat::with(['preview', 'artwork'])->find($item->id);
            $beatsArray[] = $beat;
        }
//        $beats->must(new Range('bpm', [
//            'gte' => $request->input('bpm_min') ?? 60,
//            'lte' => $request->input('bpm_max') ?? 300
//        ]));
//
//        $priceFilter = [
//            'lte' => 999999999999.0
//        ];
//        if ($request->input('price_min') && (int) $request->input('price_min') > 0) {
//            $priceFilter['gte'] =  $request->input('price_min');
//        }
//
//        if ($request->input('price_max') && (int) $request->input('price_max') > 0) {
//            $priceFilter['gte'] =  $request->input('price_max');
//        }
//
//        $beats = $beats->must(new Range(
//            'price',
//            $priceFilter
//        ))->paginate(20);

        return BeatResource::collection($beatsArray);
    }
}
