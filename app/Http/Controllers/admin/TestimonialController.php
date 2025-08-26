<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use App\Models\Testimonial;
use File;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Validator;

class TestimonialController extends Controller
{
    //
    public function index()
    {
        $testimonial = Testimonial::orderBy('created_at', 'DESC')->get();
        return response()->json(['status' => true, 'data' => $testimonial], 200);
    }

    public function show($id)
    {
        //
        $testimonial = Testimonial::find($id);
        if ($testimonial == null) {
            return response()->json(['status' => false, 'message' => 'Testimonial not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $testimonial], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'testimonial' => 'required|string|max:255',
            'citation' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $model = new Testimonial();
        $model->testimonial = $request->testimonial;
        $model->citation = $request->citation;
        $model->status = $request->status;
        $model->save();

        if ($request->imageId > 0 && $request->imageId != null) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $filename = $model->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/thumb/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $destPath = public_path('uploads/testimonial/' . $filename);
                $image = $manager->read($sourcePath);
                $image->coverDown(300, 300);
                $image->save($destPath);

                $model->image = $filename;
                $model->save();
            }
        }

        return response()->json(['status' => true, 'data' => $model, 'message' => 'Testimonial created successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);
        if ($testimonial == null) {
            return response()->json(['status' => false, 'message' => 'Testimonial not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'testimonial' => 'required|string|max:255',
            'citation' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $testimonial->testimonial = $request->testimonial;
        $testimonial->citation = $request->citation;
        $testimonial->status = $request->status;
        $testimonial->save();

        if ($request->imageId > 0 && $request->imageId != null) {
            $oldImage = $testimonial->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $filename = $testimonial->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/thumb/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $destPath = public_path('uploads/testimonial/' . $filename);
                $image = $manager->read($sourcePath);
                $image->coverDown(300, 300);
                $image->save($destPath);

                $testimonial->image = $filename;
                $testimonial->save();

                if ($oldImage != '') {
                    # code...
                    File::delete(public_path('uploads/testimonial/' . $oldImage));
                }
            }
        }

        return response()->json(['status' => true, 'data' => $testimonial, 'message' => 'Testimonial updated successfully'], 200);
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        if ($testimonial == null) {
            return response()->json(['status' => false, 'message' => 'Testimonial not found'], 404);
        }
        if ($testimonial->image != '') {
            # code...
            File::delete(public_path('uploads/testimonial/' . $testimonial->image));
        }
        $testimonial->delete();
        return response()->json(['status' => true, 'message' => 'Testimonial deleted successfully'], 200);
    }
}
