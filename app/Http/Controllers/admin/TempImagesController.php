<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempImage;

class TempImagesController extends Controller
{
    public function create(Request $request)
    {
        $image = $request->file('image'); // Use file() to get the uploaded file.

        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();
            $newName = time() . '.' . $ext;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path("temp"), $newName);

            return response()->json([
                "status" => true,
                "image_id" => $tempImage->id,
                "message" => "Image uploaded successfully"
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "No image uploaded"
            ]);
        }
    }
}
