<?php

namespace App\Http\Controllers;

use App\Events\BeatUploadedEvent;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\StoreBeatRequest;
use App\Http\Requests\UpdateBeatRequest;
use App\Http\Resources\BeatResource;
use App\Models\Beat;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BeatController extends Controller
{
    /**
     * Returns a paginated list of beats
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(FilterRequest $request)
    {
        $beats = $this->getBeatsFilter($request);

        return BeatResource::collection(
            $beats->orderBy('name')->paginate(12)
        );
    }

    /**
     * Returns the latest beats
     *
     * @param FilterRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function latest(FilterRequest $request)
    {
        $beats = $this->getBeatsFilter($request);
        return BeatResource::collection(
                $beats
                ->orderBy('created_at', 'desc')
                ->paginate(12)
        );
    }


    /**
     * Returns the trnding beats based on purchases in the last 30 days
     *
     *
     * @param FilterRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function trending(FilterRequest $request)
    {
        $beats = $this->getBeatsFilter($request);
        return BeatResource::collection(
           $beats
                ->withCount('purchases')
                ->orderBy('purchases_count', 'desc')
                ->join('beat_purchase', function ($join) {
                   $join->on('beats.id', '=', 'beat_purchase.beat_id')
                       ->whereNull('beat_purchase.deleted_at')
                       ->where('beat_purchase.created_at', '>=', now()->subDays(30));
                })
                ->limit(12)
                ->get()
        );
    }

    /**
     * Create a beat
     *
     * @authenticated
     *
     * @param  \App\Http\Requests\StoreBeatRequest  $request
     * @return BeatResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreBeatRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validationData();

        $beat = DB::transaction(function () use ($user, $data, $request) {
            $beat = $user->beats()->create([
                'name' => $data['name'],
                'description' => $data['description'],
                'bpm' => $data['bpm'],
                'is_free' => $data['is_free'],
                'is_exclusive' => $data['is_exclusive'] ?? false,
                'price' => $data['is_free'] ? 0 : (int)($data['price'] * 100),
                'user_id' => $user->id,
                'status' => Beat::STATUSES['UNPRINTED'],
            ]);


            $beat->genres()->sync($data['genres']);
            if (isset($data['parts']) && count($data['parts']) > 0) {
                $beat->parts()->sync($data['parts']);
            }

            $artwork = null;
            $original = null;
            $download = null;

            try {
                $basePath = sprintf("users/%d/beats/%d", $user->id, $beat->id);
                $artwork = Storage::putFile(
                    'public/' . $basePath,
                    $request->file('artwork'),
                    ['visibility' => 'public']
                );

                $beat->artwork()->create([
                    'public' => true,
                    'name' => $request->file('artwork')->getFilename(),
                    'file_size' => $request->file('artwork')->getSize() ? $request->file('artwork')->getSize() : 0,
                    'file_type' => $request->file('artwork')->getMimeType(),
                    'type' => 'ARTWORK',
                    'url' => $artwork
                ]);

                $original = Storage::putFile(
                    'private/' . $basePath,
                    $request->file('preview'),
                    ['visibility' => 'private']
                );

                $beat->original()->create([
                    'public' => false,
                    'name' => $request->file('preview')->getFilename(),
                    'file_size' => $request->file('preview')->getSize() ? $request->file('preview')->getSize() : 0,
                    'file_type' => $request->file('preview')->getMimeType(),
                    'type' => 'ORIGINAL',
                    'url' => $original
                ]);

                if (!$data['is_free']) {
                    $download = Storage::putFile(
                        'private/' . $basePath,
                        $request->file('download'),
                        ['visibility' => 'private']
                    );

                    $beat->download()->create([
                        'public' => false,
                        'name' => $request->file('download')->getFilename(),
                        'file_size' => $request->file('download')->getSize() ? $request->file('download')->getSize() : 0,
                        'file_type' => $request->file('download')->getMimeType(),
                        'type' => 'DOWNLOAD',
                        'url' => $download
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('issues with file upload', [
                    'beat' => $beat,
                    'exception' => $e->getMessage()
                ]);

                if ($artwork) {
                    Storage::delete($artwork);
                }

                if ($original) {
                    Storage::delete($original);
                }

                if ($download) {
                    Storage::delete($download);
                }

                throw new \Exception('failed to save your beat');
            }

            return $beat;
        });

        if ($beat) {
            BeatUploadedEvent::dispatch($beat);
        }


        return $beat ? new BeatResource($beat->load(['artwork', 'genres'])) : response()->json([
            'errors' => [
                'upload' => 'failed to save your beat'
            ]
        ]);
    }

    /**
     * Get a beat.
     *
     * @param  \App\Models\Beat  $beat
     * @return BeatResource|\Illuminate\Http\JsonResponse
     */
    public function show(Beat $beat)
    {
        if (!$beat->isAvailable()) {
            return response()->json([], 404);
        }
        return new BeatResource($beat->load(['artwork', 'genres', 'creator']));
    }

    public function download(Beat $beat)
    {
        $user = Auth::user();
        $beat = Beat::join('beat_purchase', 'beat_purchase.beat_id', '=', 'beats.id')
            ->join('purchases', 'purchases.id', '=', 'beat_purchase.purchase_id')
            ->where('purchases.user_id', '=', 8)
            ->where('beats.id', '=', 228)
            ->select('beats.*')
            ->firstOrFail();

        $download = Upload::where('beat_id', $beat->id)
            ->where('type', 'DOWNLOAD')
            ->firstOrFail();

        $url = Storage::temporaryUrl(
            $download->url, now()->addMinutes(10)
        );

        return response()->json([
            'url' => $url
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBeatRequest  $request
     * @param  \App\Models\Beat  $beat
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBeatRequest $request, Beat $beat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Beat  $beat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Beat $beat)
    {
        //
    }

    private function getBeatsFilter(FilterRequest $request)
    {
        $price_from = $request->input('price_from', 0);
        $price_to = $request->input('price_to', 9999999999);
        $genres = $request->input('genres', null);
        $bpm_from = $request->input('bpm_from', 0);
        $bpm_to = $request->input('bpm_to', 300);
        $is_free = $request->input('is_free', null);

        $beats = Beat::with(['artwork', 'preview', 'genres'])
            ->where('status', Beat::STATUSES['AVAILABLE'])
            ->whereBetween('bpm', [$bpm_from, $bpm_to]);


        if ($is_free != null) {
            $beats = $beats->where('is_free');
        } else {
            $beats = $beats->whereBetween('beats.price', [$price_from * 100, $price_to *100]);
        }

        if ($genres) {
            $beats = $beats->whereHas('genres', function (Builder $query) use ($genres) {
                $query->whereIn('genres.id', $genres);
            });
        }

        return $beats;
    }
}
