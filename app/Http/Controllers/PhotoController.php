<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Photo;
use App\Models\LikePhoto;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller {

    // Menampilkan daftar foto dari album yang dipilih
    public function index(Album $album) {
        $album->load('photos');
        return view('photos.index', compact('album'));
    }

    // Menampilkan form untuk membuat foto baru
    public function create() {
       $albums = Album::where('userID', auth()->id())->get();
       return view('photos.create', compact('albums')); 
    }

    // Menyimpan foto baru ke database
    public function store(Request $request) {
        $request->validate([
            'photo' => 'required|image|max:2048',
            'judulFoto' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'albumID' => 'required|exists:albums,albumID',
        ]);

        $photo = $request->file('photo');
        $path = $photo->store('photos', 'public');

        // Membuat entri foto baru di database
        Photo::create([
            'userID' => auth()->id(),
            'lokasiFile' => $path,
            'judulFoto' => $request->judulFoto,
            'deskripsiFoto' => $request->description,
            'tanggalUnggah' => now(),
            'albumID' => $request->albumID, 
        ]);
        // Menghilangkan pengguna ke halaman utama setelah berhasil 
        return redirect()->route('home');
    }

    public function show(Photo $photo) {
        // Menampilkan detail foto berdasarkan model yang  dipassing
    }

    // Mengupdate informasi foto
    public function update(Request $request, Photo $photo) {
       if ($photo->userID !== Auth::id()) {
        abort(403, 'Unauthorized action.');
       }
       $request->validate([
        'judulFoto' => 'required|string|max:255',
        'description' => 'nullable|string|max:255'
       ]);

       if ($request->hasFile('photo')) {
        $request->validate(['photo' => 'image|max:2048']);
        Storage::delete($photo->lokasiFile);
        $path = $request->file('photo')->store('photos', 'public');
        $photo->lokasiFile = $path;
       }

       $photo->judulFoto = $request->judulFoto;
       $photo->deskripsiFoto = $request->description;
       $photo->save();
       return redirect()->route('albums.photos', $photo->albumID);
    }

    public function edit(Photo $photo) {
        if ($photo->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $albums = Album::where('userID', Auth::id())->get();
        return view('photos.edit', compact('photo', 'albums'));
    }

    // Menghapus foto
    public function destroy(Photo $photo) {
        if ($photo->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        Storage::delete($photo->lokasiFile);
        $photo->delete();
        return redirect()->route('albums.photos', $photo->albumID);
    }

    // Menyukai atau membatalkan like pada foto
    public function like(Photo $photo) {
        if ($photo->isLikeByAuthUser()) {
            $photo->likes()->where('userID', Auth::user()->userID)->delete();
        } else {
            $photo->likes()->create([
                'userID' => Auth::user()->userID,
                'fotoID' => $photo->fotoID,
                'tanggalLike' => now(),
            ]);
        }
        return redirect()->route('home');
    }

    public function showComments(Photo $photo) {
        // Retrieve comments for the specified photo
        $comments = Comment::where('fotoID', $photo->id)->get();
        
        // Return the view with photo and comments data
        return view('photos.comment', compact('photo', 'comments'));
    }
    
    public function storeComment(Request $request, Photo $photo) {
        // Validate the comment input
        $request->validate([
            'isiKomentar' => 'required|string|max:200',
        ]);
    
        // Create a new comment associated with the photo
        Comment::create([
            'isiKomentar' => $request->isiKomentar,
            'fotoID' => $photo->fotoID,
            'userID' => Auth::id(),
        ]);
    
        // Redirect back to the comments page with a success message
        return redirect()->route('photos.comments', $photo)->with('success', 'Comment added successfully!');
    }
    
}