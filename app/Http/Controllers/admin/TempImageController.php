<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Validator;

class TempImageController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            "image"=>"required|image|mimes:jpeg,png,jpg,gif,svg",
        ]);
        if ($validator->fails()) {
            return response()->json(["status" => false ,"data"=> $validator->errors('image'), "code" => 400]);
        }
        $image = $request->image;

          $ext = $image->getClientOriginalExtension();
          $imageName = strtotime('now') . '.' . $ext;
          $model = new TempImage();
          $model->name = $imageName;
          $model->save();
          $image->move(public_path('uploads/temp'), $imageName);
          //create small thumbnail here
          $sourcePath = public_path('uploads/temp/' . $imageName);
          $destPath = public_path('uploads/temp/thumb/' . $imageName);
          $manager = new ImageManager(Driver::class);
          $image = $manager->read($sourcePath);
          $image->coverDown(300, 300);
          $image->save($destPath);
          return response()->json(["status" => true, "message" => "Image uploaded successfully" ,"data" => $model, "code" => 200]);
        
    }
}
