<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PhotoTaggingPhoto;
use App\Models\PhotoTaggingPhotoTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoTaggingPhotoController extends Controller
{
    public function index(Event $event)
    {
        $photos = $event->photoTaggingPhotos()->with('tags')->latest()->paginate(24);
        return view('admin.photo-tagging.photos.index', compact('event', 'photos'));
    }

    public function upload(Request $request, Event $event)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480', // max 20MB
        ]);

        $uploadedCount = 0;

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("photo-tagging/{$event->slug}", $filename, 'public');

                $event->photoTaggingPhotos()->create([
                    'image_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'status' => 'uploaded',
                ]);
                $uploadedCount++;
            }
        }

        return redirect()->back()->with('success', "$uploadedCount foto berhasil diupload.");
    }

    public function tags(Request $request, PhotoTaggingPhoto $photo)
    {
        $request->validate([
            'bib_numbers' => 'nullable|string', // comma separated
        ]);

        // Hapus tag lama yang manual
        $photo->tags()->where('source', 'manual')->delete();

        $bibs = array_filter(array_map('trim', explode(',', $request->bib_numbers)));
        
        foreach ($bibs as $bib) {
            // Validasi alfanumerik dasar (bisa juga di regex)
            if (preg_match('/^[a-zA-Z0-9]+$/', $bib)) {
                PhotoTaggingPhotoTag::updateOrCreate([
                    'event_id' => $photo->event_id,
                    'photo_tagging_photo_id' => $photo->id,
                    'bib_number' => $bib,
                ], [
                    'source' => 'manual',
                    'confidence' => 100,
                ]);
            }
        }

        // Update status
        if (count($bibs) > 0) {
            $photo->update(['status' => 'tagged']);
        } else {
            $photo->update(['status' => 'uploaded']); // revert jika tag dihapus
        }

        return redirect()->back()->with('success', 'Tag BIB berhasil disimpan.');
    }

    public function publish(Request $request, PhotoTaggingPhoto $photo)
    {
        if ($photo->tags()->count() === 0) {
            return redirect()->back()->with('error', 'Foto tidak bisa dipublish karena belum memiliki tag BIB.');
        }

        $photo->update(['status' => 'published']);

        return redirect()->back()->with('success', 'Foto berhasil dipublish.');
    }

    public function destroy(PhotoTaggingPhoto $photo)
    {
        if (Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }
        $photo->delete();

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }
}
