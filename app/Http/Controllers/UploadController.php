<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Terima { filename, data_url } -> simpan PNG/JPG ke storage/app/public/contents/{company}/
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'data_url' => ['required', 'string'],
        ]);

        if (! preg_match('/^data:(image\/(png|jpeg|webp));base64,(.+)$/', $data['data_url'], $m)) {
            return response()->json(['ok' => false, 'message' => 'Format gambar tidak didukung.'], 422);
        }

        $extension = match ($m[2]) {
            'jpeg' => 'jpg',
            'webp' => 'webp',
            default => 'png',
        };

        $image = base64_decode($m[3]);
        if ($image === false || strlen($image) < 100) {
            return response()->json(['ok' => false, 'message' => 'Data gambar tidak valid.'], 422);
        }

        $dir = 'contents/' . $this->companyId();
        $name = Str::slug(pathinfo($data['filename'], PATHINFO_FILENAME)) . '-' . Str::lower(Str::random(8)) . '.' . $extension;

        \Illuminate\Support\Facades\Storage::disk('public')->put($dir . '/' . $name, $image);

        return response()->json([
            'ok' => true,
            'path' => $dir . '/' . $name,
            'url' => asset('storage/' . $dir . '/' . $name),
        ]);
    }

    /**
     * Upload avatar / logo (multipart).
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'max:4096'],
            'folder' => ['nullable', 'string', 'max:64'],
        ]);

        $folder = $request->input('folder', 'company-' . $this->companyId());

        $path = $request->file('file')->store($folder, 'public');

        return response()->json([
            'ok' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }
}