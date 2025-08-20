<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\Service;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Str;
use Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy('created_at', 'DESC')->get();
        return response()->json(['status' => true, 'data' => $services], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->merge(['slug' => Str::slug($request->slug)]);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:services,slug',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $model = new Service();
        $model->title = $request->title;
        $model->slug =Str::slug($request->slug);
        $model->short_desc = $request->short_desc;
        $model->content = $request->content;
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
            $destPath = public_path('uploads/services/small/' . $filename);
            $image = $manager->read($sourcePath);
            $image->coverDown(500, 600);
            $image->save($destPath);

            // Create large thumbnail
            $destPath = public_path('uploads/services/large/' . $filename);
            $image = $manager->read($sourcePath);
            $image->scaleDown(1200);
            $image->save($destPath);

            $model->image= $filename;
            $model->save();
        }
    }
        return response()->json(['status' => true, 'message' => 'Service added successfully', 'data' => $model], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $service = Service::find($id);
        if ($service==null) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $service], 200);
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, $id)
{
    $service = Service::find($id);

    if ($service == null) {
        return response()->json(['status' => false, 'message' => 'Service not found'], 404);
    }

    $request->merge(['slug' => Str::slug($request->slug)]);

    $validator = Validator::make($request->all(), [
        'title' => 'required',
        'slug'  => 'required|unique:services,slug,' . $id,
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }

    // Update service fields
    $service->title      = $request->title;
    $service->slug       = Str::slug($request->slug);
    $service->short_desc = $request->short_desc;
    $service->content    = $request->content;
    $service->status     = $request->status;
    $service->save();

    // Save temp image if provided
    if ($request->imageId > 0 && $request->imageId != null) {
        $oldImage = $service->image;
        $tempImage = TempImage::find($request->imageId);

        if ($tempImage) {
            $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
            $filename = $service->id . '.' . $ext;

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
            $destPath = public_path('uploads/services/small/' . $filename);
            $image = $manager->read($sourcePath);
            $image->coverDown(500, 600);
            $image->save($destPath);

            // Create large thumbnail
            $destPath = public_path('uploads/services/large/' . $filename);
            $image = $manager->read($sourcePath);
            $image->scaleDown(1200);
            $image->save($destPath);

            $service->image= $filename;
            $service->save();

            if ($oldImage!='') {
                # code...
                File::delete(public_path('uploads/services/large/' . $oldImage));
                File::delete(public_path('uploads/services/small/' . $oldImage));
            }
        }
    }

    return response()->json([
        'status'  => true,
        'message' => 'Service updated successfully',
        'data'    => $service
    ], 200);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $service  = Service::find($id);
        if ($service==null) {
            # code...
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }
        File::delete(public_path('uploads/services/large/' . $service->image));
        File::delete(public_path('uploads/services/small/' . $service->image));
        $service->delete();
        return response()->json(['status' => true, 'message' => 'Service deleted successfully'], 200);
    }
}
