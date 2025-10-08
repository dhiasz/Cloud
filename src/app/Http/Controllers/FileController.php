<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{


    // Menampilkan file list user
    public function index()
    {
        $userFolder = 'users/' . Auth::id();
        $files = Storage::disk('minio')->files($userFolder);

        return view('files.index', compact('files'));
    }

    // Upload file dengan nama asli
    public function upload(Request $request)
{
    $request->validate(['file' => 'required|file']);

    $userFolder = 'users/' . Auth::id();
    $filename = $request->file('file')->getClientOriginalName();

    // Simpan ke MinIO dengan nama asli
    Storage::disk('minio')->putFileAs($userFolder, $request->file('file'), $filename);

    return back()->with('success', 'File berhasil diupload!');
}


    // Download file
    public function download($filename)
    {
        $userFolder = 'users/' . Auth::id();
        $path = $userFolder . '/' . $filename;

        if (!Storage::disk('minio')->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        $file = Storage::disk('minio')->get($path);
        $mime = Storage::disk('minio')->mimeType($path);

        return response($file)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
