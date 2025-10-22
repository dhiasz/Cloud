<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{

    
    public function folder($folder = null)
    {
        $userFolder = 'users/' . Auth::id();

        // Jika ada folder, masuk ke dalamnya
        if ($folder) {
            $userFolder .= '/' . $folder;
        }

        // Ambil semua file dan folder di path saat ini
        $files = Storage::disk('minio')->files($userFolder);
        $folders = Storage::disk('minio')->directories($userFolder);

        // Ambil nama folder saat ini untuk navigasi
        $currentFolder = $folder;

        return view('files.folder', compact('files', 'folders', 'currentFolder'));
    }


    // Menampilkan file list user
    public function index($folder = null)
    {
        $userFolder = 'users/' . Auth::id();

        // Jika ada folder, masuk ke dalamnya
        if ($folder) {
            $userFolder .= '/' . $folder;
        }

        // Ambil semua file dan folder di path saat ini
        $files = Storage::disk('minio')->files($userFolder);
        $folders = Storage::disk('minio')->directories($userFolder);

        // Ambil nama folder saat ini untuk navigasi
        $currentFolder = $folder;

        return view('files.index', compact('files', 'folders', 'currentFolder'));
    }

    // Upload file ke folder aktif
   public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:5120000', // 5GB
        ]);

        $currentFolder = $request->input('currentFolder');
        $disk = Storage::disk('minio');

        // folder dasar user
        $basePath = 'users/' . auth()->id();

        // kalau ada current folder, tambahkan ke path
        if ($currentFolder && trim($currentFolder) !== '') {
            $basePath .= '/' . $currentFolder;
        }

        // simpan file
        if ($request->hasFile('files')) {
        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            $path = $basePath . '/' . $filename;

            // Gunakan stream agar tidak memakan banyak memori
            $stream = fopen($file->getRealPath(), 'r');
            $disk->put($path, $stream);
            fclose($stream);
        }
    }


        return back()->with('success', 'Files berhasil diunggah!');
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
    public function delete(Request $request, $filename)
    {
        $userFolder = 'users/' . Auth::id();
        $currentFolder = $request->input('currentFolder');
        $path = $userFolder . ($currentFolder ? '/' . $currentFolder : '') . '/' . $filename;

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
