<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{

public function folder()
{
    $disk = Storage::disk('minio'); // atau 'local'
    $folders = $disk->directories('');
    $files = $disk->files('');
    return view('files.folder', compact('folders', 'files'));
}

    // Menampilkan file list user
    public function index()
    {
        $userFolder = 'users/' . Auth::id();

        // Ambil semua file dan folder
        $files = Storage::disk('minio')->files($userFolder);
        $folders = Storage::disk('minio')->directories($userFolder);

        return view('files.index', compact('files', 'folders'));
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

     // Preview file (gambar / pdf)
    public function preview($filename)
    {
        $userFolder = 'users/' . Auth::id();
        $path = $userFolder . '/' . $filename;

        if (!Storage::disk('minio')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('minio')->get($path);
        $mime = Storage::disk('minio')->mimeType($path);

        return response($file)->header('Content-Type', $mime);
    }

    // Hapus file
    public function delete($filename)
    {
        $userFolder = 'users/' . Auth::id();
        $path = $userFolder . '/' . $filename;

        if (Storage::disk('minio')->exists($path)) {
            Storage::disk('minio')->delete($path);
            return back()->with('success', 'File berhasil dihapus!');
        }

        return back()->with('error', 'File tidak ditemukan!');
    }

    // Buat folder
    public function createFolder(Request $request)
    {
        $request->validate(['folder_name' => 'required|string']);

        $userFolder = 'users/' . Auth::id();
        $folderName = Str::slug($request->folder_name); // supaya aman
        $folderPath = $userFolder . '/' . $folderName;

        if (!Storage::disk('minio')->exists($folderPath)) {
            Storage::disk('minio')->makeDirectory($folderPath);
            return back()->with('success', 'Folder berhasil dibuat!');
        }

        return back()->with('error', 'Folder sudah ada!');
    }
}
