<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlaylistResource;
use App\Models\Beat;
use App\Models\BeatPlaylist;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class PlaylistsController extends Controller
{
    /**
     * @apiResourceCollection App\Http\Resources\PlaylistResource
     * @apiResourceModel App\Models\Playlist
     * @authenticated
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $user = Auth::user();
        $playlists = $user->playlists()->with(['beats', 'beats.artwork', 'beats.preview','beats.genres'])->paginate(10);
        return PlaylistResource::collection($playlists);
    }

    /**
     * @apiResource App\Http\Resources\PlaylistResource
     * @apiResourceModel App\Models\Playlist
     * @authenticated
     * @param Request $request
     * @return PlaylistResource
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'beats' => ['array', 'nullable', 'sometimes'],
            'beats.*' => ['integer', 'exists:beats,id'],
            'image' => ['required', 'file', 'dimensions:min_width=500,min_height=500,max_width:1500,max_height:1500', 'mimes:jpg,bmp,png']
        ]);

        $playlist = $user->playlists()->create([
            'name' => $data['name'],
        ]);

        if (isset($data['beats']) && count($data['beats']) > 0) {
            $beats = Beat::whereIn('id', $data['beats'])
                ->where('status', Beat::STATUSES['AVAILABLE'])
                ->pluck('id');


            $playlist->beats()->attach($beats->all());
        }

        $artwork = null;

        try {
            $basePath = sprintf("users/%d/playlists/%d", $user->id, $playlist->id);

            $artwork = Storage::putFile(
                'public/' . $basePath,
                $request->file('image'),
                ['visibility' => 'public']
            );

            $playlist->image()->create([
                'public' => true,
                'name' => $request->file('image')->getFilename(),
                'file_size' => $request->file('image')->getSize() ? $request->file('image')->getSize() : 0,
                'file_type' => $request->file('image')->getMimeType(),
                'type' => 'PLAYLIST_ARTWORK',
                'url' => $artwork
            ]);
        } catch (\Exception $e) {
            Log::error('issues with playlist image upload', [
                'playlist' => $playlist,
                'exception' => $e->getMessage()
            ]);

            if ($artwork) {
                Storage::delete($artwork);
            }

            throw new \Exception('failed to save your beat');
        }

        $playlist->load(['beats', 'beats.artwork', 'beats.preview', 'image', 'beats.genres']);
        return new PlaylistResource($playlist);
    }

    /**
     * @authenticated
     * @apiResource App\Http\Resources\PlaylistResource
     * @apiResourceModel App\Models\Playlist
     *
     * @param Playlist $playlist
     * @param Request $request
     * @return PlaylistResource
     */
    public function add(Playlist $playlist, Request $request)
    {
        $user = Auth::user();

        if ($playlist->user_id !== $user->id) {
            throw new UnauthorizedHttpException('You are not allowed to update this playlist');
        }

        $data = $request->validate([
            'beats' => ['array', 'required'],
            'beats.*' => ['integer', 'exists:beats,id']
        ]);

        $playlist->beats()->syncWithoutDetaching($data['beats']);
        $playlist->load(['beats', 'beats.artwork', 'beats.preview', 'beats.genres']);
        return new PlaylistResource($playlist);
    }

    /**
     * @authenticated
     * @apiResource App\Http\Resources\PlaylistResource
     * @apiResourceModel App\Models\Playlist
     * @param Playlist $playlist
     * @return PlaylistResource
     */
    public function show(Playlist $playlist)
    {
        $user = Auth::user();

        if ($playlist->user_id !== $user->id) {
            throw new UnauthorizedHttpException('You are not allowed to update this playlist');
        }

        $playlist->load(['beats', 'beats.artwork', 'beats.preview', 'beats.genres']);
        return new PlaylistResource($playlist);
    }

    /**
     * @authenticated
     * @apiResource App\Http\Resources\PlaylistResource
     * @apiResourceModel App\Models\Playlist
     *
     * @param Playlist $playlist
     * @param Request $request
     * @return PlaylistResource
     */
    public function remove(Playlist $playlist, Request $request)
    {
        $user = Auth::user();

        if ($playlist->user_id !== $user->id) {
            throw new UnauthorizedHttpException('You are not allowed to update this playlist');
        }

        $data = $request->validate([
            'beats' => ['array', 'required'],
            'beats.*' => ['integer', 'exists:beats,id']
        ]);

        $playlist->beats()->detach($data['beats']);
        $playlist->load(['beats', 'beats.artwork', 'beats.preview', 'beats.genres']);
        return new PlaylistResource($playlist);
    }

    /**
     * @authenticated
     * @apiResource App\Http\Resources\PlaylistResource
     * @apiResourceModel App\Models\Playlist
     * @param Request $request
     * @param Playlist $playlist
     * @return PlaylistResource
     */
    public function update(Request $request, Playlist $playlist)
    {
        $user = Auth::user();

        if ($playlist->user_id !== $user->id) {
            throw new UnauthorizedHttpException('You are not allowed to update this playlist');
        }

        $data = $request->validate([
            'name' => ['string', 'required', 'min:3'],
        ]);

        $playlist->name = $data['name'];
        $playlist->save();
        $playlist->load(['beats', 'beats.artwork', 'beats.preview', 'beats.genres']);
        return new PlaylistResource($playlist);
    }

    /**
     * @authenticated
     * @response scenario=success {}
     *
     * @param Playlist $playlist
     * @return void
     */
    public function destroy(Playlist $playlist)
    {
        $user = Auth::user();

        if ($playlist->user_id !== $user->id) {
            throw new UnauthorizedHttpException('You are not allowed to update this playlist');
        }

        $playlist->beats()->detach();
        $playlist->delete();
    }
}
