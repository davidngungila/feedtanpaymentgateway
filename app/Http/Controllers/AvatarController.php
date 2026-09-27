<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function show(Request $request, string $file)
    {
        $path = 'avatars/'.ltrim($file, '/');

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $response = Storage::disk('public')->response($path);
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        $response->headers->set('Content-Type', Storage::disk('public')->mimeType($path) ?? 'application/octet-stream');

        return $response;
    }
}
