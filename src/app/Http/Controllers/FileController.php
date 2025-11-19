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
        if ($folder) {
            $userFolder .= '/' . $folder;
        }

        // ambil semua (raw)
        $rawFiles = Storage::disk('minio')->files($userFolder);
        $rawFolders = Storage::disk('minio')->directories($userFolder);

        $trashPrefix = trim('users/' . Auth::id() . '/.trash', '/');


        $filterOutTrash = function($path) use ($trashPrefix) {

            $p = rtrim($path, '/');

            if (Str::contains($p, $trashPrefix . '/')) return false;
            if (Str::startsWith($p, $trashPrefix)) return false;
            return true;
        };


        $files = array_values(array_filter($rawFiles, $filterOutTrash));
        $folders = array_values(array_filter($rawFolders, $filterOutTrash));

        $currentFolder = $folder;

        return view('files.folder', compact('files', 'folders', 'currentFolder'));
    }


    // Menampilkan file list user
    public function index($folder = null)
    {
        $userFolder = 'users/' . Auth::id();
        if ($folder) {
            $userFolder .= '/' . $folder;
        }

        // ambil semua (raw)
        $rawFiles = Storage::disk('minio')->files($userFolder);
        $rawFolders = Storage::disk('minio')->directories($userFolder);

        $trashPrefix = trim('users/' . Auth::id() . '/.trash', '/');


        $filterOutTrash = function($path) use ($trashPrefix) {

            $p = rtrim($path, '/');

            if (Str::contains($p, $trashPrefix . '/')) return false;
            if (Str::startsWith($p, $trashPrefix)) return false;
            return true;
        };


        $files = array_values(array_filter($rawFiles, $filterOutTrash));
        $folders = array_values(array_filter($rawFolders, $filterOutTrash));

        $currentFolder = $folder;

        return view('files.index', compact('files', 'folders', 'currentFolder'));
    }

    // Upload file ke folder aktif
    public function upload(Request $request)
    {
        // Validasi dasar tetap seperti sebelumnya...
        $disk = Storage::disk('minio');

        // Ambil dirs (folder markers) jika ada
        $dirsJson = $request->input('dirs', '[]');
        $dirs = json_decode($dirsJson, true);
        if (!is_array($dirs)) $dirs = [];

        $currentFolder = $request->input('currentFolder');
        $baseUserPath = 'users/' . auth()->id();
        if ($currentFolder && trim($currentFolder) !== '') {
            $currentFolder = trim($currentFolder, '/');
            $baseUserPathWithCurrent = $baseUserPath . '/' . $currentFolder;
        } else {
            $baseUserPathWithCurrent = $baseUserPath;
        }

        // 1) Buat folder markers dulu (jika ada)
        foreach ($dirs as $dir) {
            $dir = str_replace('\\', '/', $dir);
            $dir = preg_replace('#\.\./#', '', $dir);
            $dir = trim($dir, '/'); // tanpa trailing slash

            if ($dir === '') continue;

            $targetDir = $baseUserPathWithCurrent . '/' . $dir;
            try {
                $disk->makeDirectory($targetDir);
            } catch (\Throwable $e) {
                // fallback: buat marker object dengan trailing slash
                try {
                    $markerPath = rtrim($targetDir, '/') . '/';
                    $disk->put($markerPath, '');
                } catch (\Throwable $e2) {
                    \Log::warning("Failed create dir marker [$targetDir]: " . $e2->getMessage());
                }
            }
        }

        // 2) Ambil files[] dan paths[] dari request
        $uploadedFiles = $request->file('files', []); // array of UploadedFile
        $paths = $request->input('paths', []);         // array of relative paths matching order

        // Jika tidak ada files tapi ada file input name 'file' (single), fallback
        if (empty($uploadedFiles) && $request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
            $paths = [$request->file('file')->getClientOriginalName()];
        }

        // Pastikan jumlahnya sama; jika tidak sama, tutup dengan error atau pad dengan nama file sederhana
        // Kita akan pair berdasarkan index
        $countFiles = count($uploadedFiles);
        for ($i = 0; $i < $countFiles; $i++) {
            $file = $uploadedFiles[$i];
            $relPath = isset($paths[$i]) ? $paths[$i] : $file->getClientOriginalName();

            // sanitize path
            $relPath = str_replace('\\', '/', $relPath);
            $relPath = preg_replace('#\.\./#', '', $relPath);
            $relPath = ltrim($relPath, '/');

            // jika currentFolder ada, prefix
            $finalRelative = ($currentFolder && trim($currentFolder) !== '') ? ($currentFolder . '/' . $relPath) : $relPath;

            $putPath = $baseUserPath . '/' . $finalRelative;

            // buat direktori marker jika perlu (opsional)
            $dirOfFile = trim(dirname($putPath), '/');
            // makeDirectory untuk memastikan prefix ada (adapter s3 biasanya tidak perlu, tapi aman)
            try {
                $disk->makeDirectory($dirOfFile);
            } catch (\Throwable $e) {
                // ignore
            }

            // simpan file (stream)
            $stream = fopen($file->getRealPath(), 'r');
            $disk->put($putPath, $stream);
            if (is_resource($stream)) fclose($stream);
        }

        return back()->with('success', 'Files & folders berhasil diunggah!');
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

        // normalisasi input: hapus leading users/{id}/ jika ada
        $filename = trim($filename, '/');
        $prefixToStrip = 'users/' . Auth::id() . '/';
        if (Str::startsWith($filename, $prefixToStrip)) {
            $filename = substr($filename, strlen($prefixToStrip));
            $filename = trim($filename, '/');
        }

        $currentFolder = $request->input('currentFolder');
        $relative = $filename;

        // jika yang dikirim adalah basename dan ada currentFolder => buat path relatif
        if (!Str::contains($relative, '/') && $currentFolder && trim($currentFolder) !== '') {
            $relative = trim($currentFolder, '/') . '/' . $relative;
        }
        $relative = trim($relative, '/');

        // fullPath tanpa trailing slash
        $fullPath = trim($userFolder . ($relative !== '' ? '/' . $relative : ''), '/');

        \Log::info("Delete requested. fullPath={$fullPath}, relative={$relative}, currentFolder={$currentFolder}");

        // prefix dengan trailing slash untuk listing semua objects di bawahnya
        $prefixWithSlash = $fullPath === '' ? '' : $fullPath . '/';

        $objectsToDelete = [];

        // 1) Cari semua objek di bawah prefixWithSlash (recommended)
        try {
            if ($prefixWithSlash !== '') {
                $found = $disk->allFiles($prefixWithSlash);
                if (!empty($found)) {
                    $objectsToDelete = array_merge($objectsToDelete, $found);
                }
                // juga periksa files() untuk beberapa adapter
                $foundFiles = $disk->files($prefixWithSlash);
                if (!empty($foundFiles)) {
                    $objectsToDelete = array_merge($objectsToDelete, $foundFiles);
                }
            }

            // 2) Jika tidak ada hasil, coba listing tanpa slash (beberapa adapter menyimpan tanpa)
            if (empty($objectsToDelete)) {
                $found2 = $disk->allFiles($fullPath);
                if (!empty($found2)) {
                    $objectsToDelete = array_merge($objectsToDelete, $found2);
                }
                $foundFiles2 = $disk->files($fullPath);
                if (!empty($foundFiles2)) {
                    $objectsToDelete = array_merge($objectsToDelete, $foundFiles2);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Listing failed for [$fullPath] or [$prefixWithSlash]: " . $e->getMessage());
        }

        // normalisasi unik
        $objectsToDelete = array_values(array_unique($objectsToDelete));

        $deleted = [];
        $failed = [];

        if (!empty($objectsToDelete)) {
            foreach ($objectsToDelete as $obj) {
                try {
                    $disk->delete($obj);
                    if ($disk->exists($obj)) {
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

            // Coba hapus directory marker (beberapa adapter punya marker dengan trailing slash)
            if ($prefixWithSlash !== '') {
                try {
                    if ($disk->exists($prefixWithSlash)) {
                        $disk->delete($prefixWithSlash);
                    }
                    // dan coba deleteDirectory sebagai cleanup
                    if (method_exists($disk, 'deleteDirectory')) {
                        $disk->deleteDirectory($fullPath);
                    }
                } catch (\Throwable $e) {
                    \Log::info("Post-cleanup deleteDirectory/marker failed for [$fullPath]: " . $e->getMessage());
                }
            }

            $msg = '';
            if (!empty($deleted)) {
                $msg .= 'Berhasil menghapus ' . count($deleted) . ' objek. ';
            }
            if (!empty($failed)) {
                $msg .= 'Gagal menghapus ' . count($failed) . ' objek. ';
            }

            if (!empty($failed)) {
                \Log::error('Objects failed to delete: ' . implode(', ', $failed));
                return back()->with('error', $msg . 'Lihat laravel.log untuk rincian.');
            }

            return back()->with('success', $msg);
        }

        // Tidak ada objek di bawah prefix => coba hapus file tunggal (bisa jadi marker tanpa slash)
        try {
            if ($disk->exists($fullPath)) {
                $disk->delete($fullPath);
                return back()->with('success', 'File berhasil dihapus!');
            }

            // coba trailing slash marker
            if ($prefixWithSlash !== '' && $disk->exists($prefixWithSlash)) {
                $disk->delete($prefixWithSlash);
                // juga try deleteDirectory
                if (method_exists($disk, 'deleteDirectory')) {
                    $disk->deleteDirectory($fullPath);
                }
                return back()->with('success', 'Folder (marker) berhasil dihapus!');
            }
        } catch (\Throwable $e) {
            \Log::error("Single/marker delete failed for [$fullPath|$prefixWithSlash]: " . $e->getMessage());
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
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

    //Ini bagian TERBARU untuk move to trash
    public function moveToTrash(Request $request, $path = null)
{
    $disk = Storage::disk('minio');
    $path = urldecode($path ?? $request->input('path', ''));
    $path = trim($path, '/');

    $userPrefix = 'users/' . Auth::id();
    // If path already includes user prefix, strip it
    if (Str::startsWith($path, $userPrefix)) {
        $path = trim(substr($path, strlen($userPrefix)), '/');
    }

    if ($path === '') {
        return back()->with('error', 'Path tidak valid');
    }

    $sourcePrefix = $userPrefix . '/' . $path;
    $trashPrefix = $userPrefix . '/.trash/' . $path; // simpan di users/{id}/.trash/<same-path>

    try {
        // Kumpulkan semua objek di bawah sourcePrefix (jika folder) atau single file
        $objects = [];
        try {
            $objects = $disk->allFiles($sourcePrefix);
        } catch (\Throwable $e) {
            // allFiles kadang gagal jika path is file; fallback ke files()
            try {
                $objects = $disk->files($sourcePrefix);
            } catch (\Throwable $e2) {
                $objects = [];
            }
        }

        if (empty($objects)) {
            // mungkin single file path (exact)
            if ($disk->exists($sourcePrefix)) {
                $objects = [$sourcePrefix];
            } else {
                // try with trailing slash variants
                $alt = rtrim($sourcePrefix, '/') . '/';
                if ($disk->exists($alt)) {
                    $objects = [$alt];
                }
            }
        }

        if (empty($objects)) {
            return back()->with('error', 'Tidak ada objek ditemukan untuk dipindahkan.');
        }

        $moved = [];
        $failed = [];

        foreach ($objects as $obj) {
            // hitung relative path after users/{id}/
            $rel = ltrim(substr($obj, strlen($userPrefix)), '/'); // e.g. folder1/file.txt
            $target = $userPrefix . '/.trash/' . $rel;

            try {
                // stream copy: buka sumber -> put ke target
                $stream = $disk->readStream($obj);
                if ($stream !== false) {
                    $disk->put($target, $stream);
                    if (is_resource($stream)) fclose($stream);
                } else {
                    // fallback copy via get/put
                    $contents = $disk->get($obj);
                    $disk->put($target, $contents);
                }

                // hapus sumber
                $disk->delete($obj);

                $moved[] = $obj;
            } catch (\Throwable $e) {
                \Log::error("MoveToTrash failed for $obj: " . $e->getMessage());
                $failed[] = $obj;
            }
        }

        // Optionally attempt to delete directory markers
        if (method_exists($disk, 'deleteDirectory')) {
            try {
                $disk->deleteDirectory($sourcePrefix);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $msg = '';
        if (!empty($moved)) $msg .= 'Berhasil memindahkan ' . count($moved) . ' objek ke sampah. ';
        if (!empty($failed)) $msg .= 'Gagal memindahkan ' . count($failed) . ' objek.';

        if (!empty($failed)) {
            \Log::error('MoveToTrash failed objects: ' . implode(', ', $failed));
            return back()->with('error', $msg . ' Lihat laravel.log untuk detail.');
        }

        return back()->with('success', $msg);

    } catch (\Throwable $e) {
        \Log::error("moveToTrash error for [$sourcePrefix]: " . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat memindahkan ke sampah.');
    }
}

/**
 * Show user's trash contents
 */
/**
 * Show user's trash contents (browseable)
 * Accepts optional folder path inside .trash
 */
public function trashIndex($folder = null)
{
    $disk = Storage::disk('minio');
    $userPrefix = 'users/' . Auth::id();
    $trashBase = trim($userPrefix . '/.trash', '/'); // users/{id}/.trash

    // jika ada folder yang diminta, masuk ke dalamnya
    $folder = $folder ? trim($folder, '/') : null;
    $listPrefix = $trashBase . ($folder ? '/' . $folder : '');

    // normalize: jika prefix kosong, pakai trashBase
    $listPrefix = trim($listPrefix, '/');

    // catch exceptions saat listing
    try {
        // ambil files & folders di level prefix (tidak recursive untuk directories)
        $filesRaw = $disk->files($listPrefix) ?: [];
        $foldersRaw = $disk->directories($listPrefix) ?: [];
    } catch (\Throwable $e) {
        // fallback ke allFiles jika provider behave differently
        try {
            $filesRaw = $disk->allFiles($listPrefix) ?: [];
            $foldersRaw = $disk->directories($listPrefix) ?: [];
        } catch (\Throwable $e2) {
            $filesRaw = [];
            $foldersRaw = [];
            \Log::warning("trashIndex listing failed for [$listPrefix]: " . $e2->getMessage());
        }
    }

    // convert full paths to relative paths inside .trash (mis. folder1/file.png)
    $stripPrefix = $trashBase . '/';
    $relFiles = array_map(function($p) use ($stripPrefix) {
        return ltrim(Str::startsWith($p, $stripPrefix) ? substr($p, strlen($stripPrefix)) : $p, '/');
    }, $filesRaw);

    $relFolders = array_map(function($p) use ($stripPrefix) {
        return ltrim(Str::startsWith($p, $stripPrefix) ? substr($p, strlen($stripPrefix)) : $p, '/');
    }, $foldersRaw);

    // Sort optionally
    sort($relFolders);
    sort($relFiles);

    // currentFolder untuk breadcrumb (null untuk root .trash)
    $currentFolder = $folder;

    return view('files.sampah', [
        'files' => $relFiles,
        'folders' => $relFolders,
        'currentFolder' => $currentFolder
    ]);
}


/**
 * Restore file/folder from trash back to user root (move)
 */
public function restoreFromTrash(Request $request, $path = null)
{
    $disk = Storage::disk('minio');
    $path = urldecode($path ?? $request->input('path', ''));
    $path = trim($path, '/');

    $userPrefix = 'users/' . Auth::id();
    $trashPrefix = $userPrefix . '/.trash/' . $path;
    $targetPrefix = $userPrefix . '/' . $path;

    // collect objects under trashPrefix
    $objects = [];
    try {
        $objects = $disk->allFiles($trashPrefix);
    } catch (\Throwable $e) {
        $objects = $disk->files($trashPrefix) ?? [];
    }

    if (empty($objects)) {
        // maybe single file
        if ($disk->exists($trashPrefix)) {
            $objects = [$trashPrefix];
        } else {
            return back()->with('error', 'Tidak ada objek ditemukan di sampah.');
        }
    }

    $restored = [];
    $failed = [];
    foreach ($objects as $obj) {
        try {
            $rel = ltrim(substr($obj, strlen($userPrefix . '/.trash/')), '/');
            $target = $userPrefix . '/' . $rel;

            $stream = $disk->readStream($obj);
            if ($stream !== false) {
                $disk->put($target, $stream);
                if (is_resource($stream)) fclose($stream);
            } else {
                $contents = $disk->get($obj);
                $disk->put($target, $contents);
            }

            // hapus dari trash
            $disk->delete($obj);
            $restored[] = $obj;
        } catch (\Throwable $e) {
            \Log::error("Restore failed for $obj: " . $e->getMessage());
            $failed[] = $obj;
        }
    }

    if (!empty($failed)) {
        \Log::error('Restore failed objects: ' . implode(', ', $failed));
        return back()->with('error', 'Beberapa item gagal dipulihkan. Lihat laravel.log.');
    }

    return back()->with('success', 'Restore berhasil: ' . count($restored) . ' objek.');
}

/**
 * Permanently delete file/folder from trash
 */
public function forceDeleteFromTrash(Request $request, $path = null)
{
    $disk = Storage::disk('minio');
    $path = urldecode($path ?? $request->input('path', ''));
    $path = trim($path, '/');

    $userPrefix = 'users/' . Auth::id();
    $trashPrefix = $userPrefix . '/.trash/' . $path;

    // collect objects
    $objects = [];
    try {
        $objects = $disk->allFiles($trashPrefix);
    } catch (\Throwable $e) {
        $objects = $disk->files($trashPrefix) ?? [];
    }

    if (empty($objects)) {
        if ($disk->exists($trashPrefix)) {
            $objects = [$trashPrefix];
        } else {
            return back()->with('error', 'Tidak ada item ditemukan untuk dihapus permanen.');
        }
    }

    $deleted = [];
    $failed = [];

    foreach ($objects as $obj) {
        try {
            $disk->delete($obj);
            if ($disk->exists($obj)) {
                $failed[] = $obj;
            } else {
                $deleted[] = $obj;
            }
        } catch (\Throwable $e) {
            \Log::error("Force delete failed for $obj: " . $e->getMessage());
            $failed[] = $obj;
        }
    }

    // attempt deleteDirectory cleanup
    if (method_exists($disk, 'deleteDirectory')) {
        try {
            $disk->deleteDirectory($trashPrefix);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    if (!empty($failed)) {
        \Log::error('Force delete failed objects: ' . implode(', ', $failed));
        return back()->with('error', 'Beberapa objek gagal dihapus permanen.');
    }

    return back()->with('success', 'Berhasil menghapus permanen ' . count($deleted) . ' objek.');
}
public function previewTrash($path)
{
    $disk = Storage::disk('minio');
    $path = urldecode($path);
    $path = trim($path, '/');

    $userPrefix = 'users/' . Auth::id();

    // full path in minio
    $fullPath = $userPrefix . '/.trash/' . $path;
    $fullPath = trim($fullPath, '/');

    if (!$disk->exists($fullPath)) {
        abort(404, 'File tidak ditemukan di sampah');
    }

    try {
        $file = $disk->get($fullPath);
        $mime = $disk->mimeType($fullPath);
        return response($file)->header('Content-Type', $mime);
    } catch (\Throwable $e) {
        \Log::error("previewTrash failed for [$fullPath]: " . $e->getMessage());
        abort(500, 'Gagal membuka file');
    }
}
}