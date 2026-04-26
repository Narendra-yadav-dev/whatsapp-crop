<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function showCropForm()
    {
        return view('crop');
    }
    public function upload(Request $request)
    {
        $image = $request->cropped_image;
        $image_parts = explode(';base64,', $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = time().'.jpg';
        Storage::disk('public')->put('uploads/'.$fileName, $image_base64);
        return response()->json([
            'success' => true,
            'image' => $fileName
        ]);
    }
}
