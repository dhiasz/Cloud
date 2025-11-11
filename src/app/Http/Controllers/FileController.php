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
    // Terima baik 'file' (single) atau 'files' (multiple)
    // Validasi manual supaya fleksibel
    $filesToProcess = [];

    if ($request->hasFile('files')) {
        $filesToProcess = $request->file('files');
    } elseif ($request->hasFile('file')) {
        // fallback single file input dari layout
        $filesToProcess = [$request->file('file')];
    }

    if (empty($filesToProcess)) {
        return back()->with('error', 'Tidak ada file yang diunggah.');
    }

    // Validasi ukuran setiap file (opsional, ubah sesuai kebutuhan)
    foreach ($filesToProcess as $f) {
        if (!$f->isValid()) {
            return back()->with('error', 'Salah satu file tidak valid.');
        }
        // contoh validasi ukuran: 5GB => 5120000 KB pada konfigurasi awalmu
        // tapi disarankan ubah ke bytes jika mau presisi
    }

    $currentFolder = $request->input('currentFolder');
    $disk = Storage::disk('minio');
    $basePath = 'users/' . auth()->id();

    if ($currentFolder && trim($currentFolder) !== '') {
        // pastikan tidak ada leading/trailing slash
        $currentFolder = trim($currentFolder, '/');
        $basePath .= '/' . $currentFolder;
    }

    foreach ($filesToProcess as $file) {
        $filename = $file->getClientOriginalName();
        $path = $basePath . '/' . $filename;
        $stream = fopen($file->getRealPath(), 'r');
        $disk->put($path, $stream);
        if (is_resource($stream)) fclose($stream);
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
        $folderName = Str::slug($request->folder_name);
        $currentFolder = $request->input('currentFolder');

        // buat path target (jika currentFolder ada, buat di dalamnya)
        if ($currentFolder && trim($currentFolder) !== '') {
            $currentFolder = trim($currentFolder, '/');
            $folderPath = $userFolder . '/' . $currentFolder . '/' . $folderName;
        } else {
            $folderPath = $userFolder . '/' . $folderName;
        }

        // langsung buat directory tanpa cek exists (lebih sederhana)
        Storage::disk('minio')->makeDirectory($folderPath);

        return back()->with('success', 'Folder berhasil dibuat!');
    }

}