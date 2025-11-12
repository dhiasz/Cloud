<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

    // Hapus file & folder
    public function delete(Request $request, $filename)
    {
        $filename = urldecode($filename);
        $disk = Storage::disk('minio');
        $userFolder = 'users/' . Auth::id();

        $filename = trim($filename, '/');
        $prefixToStrip = 'users/' . Auth::id() . '/';
        if (Str::startsWith($filename, $prefixToStrip)) {
            $filename = substr($filename, strlen($prefixToStrip));
            $filename = trim($filename, '/');
        }

        $currentFolder = $request->input('currentFolder');
        $relative = $filename;
        if (!Str::contains($relative, '/') && $currentFolder && trim($currentFolder) !== '') {
            $relative = trim($currentFolder, '/') . '/' . $relative;
        }
        $relative = trim($relative, '/');

        $fullPath = $userFolder . ($relative !== '' ? '/' . $relative : '');
        $fullPath = trim($fullPath, '/');

        \Log::info("Delete requested (aggressive). fullPath={$fullPath}, relative={$relative}, currentFolder={$currentFolder}");

        // Kumpulkan kemungkinan objek (prefix variants)
        $variants = [
            $fullPath,
            $fullPath . '/',
        ];

        $objectsToDelete = [];
        foreach ($variants as $v) {
            try {
                $found = $disk->allFiles($v);
                if (!empty($found)) {
                    $objectsToDelete = array_merge($objectsToDelete, $found);
                }
                // juga cek files() untuk memastikan
                $foundFiles = $disk->files($v);
                if (!empty($foundFiles)) {
                    $objectsToDelete = array_merge($objectsToDelete, $foundFiles);
                }
            } catch (\Throwable $e) {
                \Log::warning("Listing failed for [$v]: " . $e->getMessage());
            }
        }

        // Normalisasi unik
        $objectsToDelete = array_values(array_unique($objectsToDelete));

        $deleted = [];
        $failed = [];

        // Jika ada objek, hapus satu-per-satu dan verifikasi
        if (!empty($objectsToDelete)) {
            foreach ($objectsToDelete as $obj) {
                try {
                    $res = $disk->delete($obj);
                    // beberapa adapter mengembalikan true/false, beberapa tidak => periksa exists()
                    if ($disk->exists($obj)) {
                        // masih ada setelah delete -> catat gagal
                        $failed[] = $obj;
                        \Log::warning("After delete(), object still exists: $obj");
                    } else {
                        $deleted[] = $obj;
                        \Log::info("Deleted object: $obj");
                    }
                } catch (\Throwable $e) {
                    $failed[] = $obj;
                    \Log::error("Delete failed for $obj: " . $e->getMessage());
                }
            }

            // Coba deleteDirectory untuk bersihkan prefix jika tersedia
            if (method_exists($disk, 'deleteDirectory')) {
                try {
                    $disk->deleteDirectory($fullPath);
                } catch (\Throwable $e) {
                    \Log::info("deleteDirectory post-cleanup failed for [$fullPath]: " . $e->getMessage());
                }
            }

            // Build pesan hasil
            $msg = '';
            if (!empty($deleted)) {
                $msg .= 'Berhasil menghapus ' . count($deleted) . ' objek. ';
            }
            if (!empty($failed)) {
                $msg .= 'Gagal menghapus ' . count($failed) . ' objek. ';
            }

            if (!empty($failed)) {
                // simpan daftar gagal ke log agar bisa Anda telusuri
                \Log::error('Objects failed to delete: ' . implode(', ', $failed));
                return back()->with('error', $msg . 'Lihat laravel.log untuk rincian.');
            }

            return back()->with('success', $msg);
        }

        // Jika tidak ditemukan objek, coba hapus file tunggal / marker
        try {
            if ($disk->exists($fullPath)) {
                try {
                    $disk->delete($fullPath);
                    return back()->with('success', 'File berhasil dihapus!');
                } catch (\Throwable $e) {
                    \Log::error("Single-file delete failed for [$fullPath]: " . $e->getMessage());
                    return back()->with('error', 'Gagal menghapus file: ' . $e->getMessage());
                }
            }

            // cek marker
            $dirMarker = $fullPath . '/';
            if ($disk->exists($dirMarker)) {
                try {
                    $disk->delete($dirMarker);
                    return back()->with('success', 'Folder (marker) berhasil dihapus!');
                } catch (\Throwable $e) {
                    \Log::error("Dir marker delete failed for [$dirMarker]: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("exists() check failed for [$fullPath] or [$dirMarker]: " . $e->getMessage());
        }

        \Log::info("Nothing to delete for fullPath={$fullPath}");
        return back()->with('error', 'File/Folder tidak ditemukan atau sudah kosong. Periksa laravel.log untuk detail.');
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