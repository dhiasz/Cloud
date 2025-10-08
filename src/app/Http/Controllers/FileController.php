<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userFolder = 'users/' . Auth::id();
        $files = Storage::disk('minio')->files($userFolder);

        return view('files.index', compact('files'));
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file']);

        $userFolder = 'users/' . Auth::id();

        // Simpan file dengan nama asli
        $filename = $request->file('file')->getClientOriginalName();
        $path = $userFolder . '/' . $filename;

        Storage::disk('minio')->put($path, file_get_contents($request->file('file')));

        return back()->with('success', 'File berhasil diupload!');
    }

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
