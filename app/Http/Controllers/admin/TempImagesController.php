<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempImage;
use Intervention\Image\Facades\Image;

class TempImagesController extends Controller
{
    public function create(Request $request)
    {
        
        if ($request->image) {
            $image = $request->image;
            $extension = $image->getClientOriginalExtension();
            $newName = time() . '.' . $extension;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path().'/temp',$newName);

            // Generate Thumbnail 
            $sourcePath = public_path().'/temp/'.$newName; 
            $destPath = public_path().'/temp/thumb/'.$newName; 
            $thumbnail = Image::make($sourcePath);
            $thumbnail->fit(300, 275);
            $thumbnail->save($destPath); 

            return response()->json([
                "status" => true,
                "image_id" => $tempImage->id,
                "ImagePath" => asset('/temp/thumb/' . $newName),
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
