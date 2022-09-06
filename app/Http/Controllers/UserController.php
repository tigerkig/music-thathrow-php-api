<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(UpdateUserRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $userData = [];

        if (is_string($validated['name'])) {
            $userData['name'] = $validated['name'];
        }

        if (is_string($validated['first_name'])) {
            $userData['first_name'] = $validated['first_name'];
        }

        if (is_string($validated['last_name'])) {
            $userData['last_name'] = $validated['last_name'];
        }

        if (count($userData) > 0) {
            $user->update($userData);
        }

        if (!is_null($validated['profile_picture'])) {
            try {
                $basePath = sprintf("users/%d/profile", $user->id);
                $image = Storage::putFile('public/' . $basePath, $request->file('profile_picture'), ['visibility' => 'public']);
                $user->profileImage()->create([
                    'public' => true,
                    'name' => $request->file('profile_picture')->getFilename(),
                    'file_size' => $request->file('profile_picture')->getSize() ? $request->file('profile_picture')->getSize() : 0,
                    'file_type' => $request->file('profile_picture')->getMimeType(),
                    'type' => 'USER_PICTURE',
                    'url' => $image
                ]);
            } catch (\Exception $e) {
                Log::error('issues with profile image upload', [
                    'user' => $user->id,
                    'exception' => $e->getMessage()
                ]);

                report($e);
            }
        }

        $user = $user->fresh();
        return new UserResource($user);
    }
}
