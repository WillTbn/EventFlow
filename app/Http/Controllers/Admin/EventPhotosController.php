<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Photos\StoreEventMainPhoto;
use App\Actions\Photos\StoreEventPhotos;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventPhotosRequest;
use App\Http\Requests\Admin\UpdateEventMainPhotoRequest;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class EventPhotosController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store the main photo for the event.
     */
    public function updateMain(
        UpdateEventMainPhotoRequest $request,
        Event $event,
        StoreEventMainPhoto $storeEventMainPhoto
    ): RedirectResponse {
        $this->authorize('update', $event);

        $photo = $request->file('main_photo');
        $storeEventMainPhoto->handle($event, $photo);

        return back();
    }

    /**
     * Store story photos for the event after it ends.
     */
    public function store(
        StoreEventPhotosRequest $request,
        Event $event,
        StoreEventPhotos $storeEventPhotos
    ): RedirectResponse {
        $this->authorize('addPhotos', $event);

        $photos = $request->file('photos', []);
        $storeEventPhotos->handle($event, $request->user(), $photos);

        return back();
    }
}
