<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Str;

class ProjectController extends Controller
{
    // this method will return all projects
    public function index()
    {
        $projects = Project::orderBy('created_at', 'DESC')->get();
        return response()->json(['status' => true, 'data' => $projects], 200);
    }

    // this will insert project in database
    public function store(Request $request)
    {
        $request->merge(['slug' => Str::slug($request->slug)]);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:projects,slug',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $project = new Project();
        $project->title = $request->title;
        $project->slug = Str::slug($request->slug);
        $project->short_desc = $request->short_desc;
        $project->content = $request->content;
        $project->construction_type = $request->construction_type;
        $project->sector = $request->sector;
        $project->location = $request->location;
        $project->status = $request->status;
        $project->save();


        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $exArr = explode('.', $tempImage->name);
                $ext = last($exArr);
                $filename = strtotime('now') . $project->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $destPath = public_path('uploads/projects/small/' . $filename);
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $image = $manager->read($sourcePath);
                $image->coverDown(500, 600);
                $image->save($destPath);

                // Create large thumbnail
                $destPath = public_path('uploads/projects/large/' . $filename);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200);
                $image->save($destPath);

                $project->image = $filename;
                $project->save();

            }
        }

        return response()->json(['status' => true, 'message' => 'Project added successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        //
        $project = Project::find($id);

        if ($project == null) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $request->merge(['slug' => Str::slug($request->slug)]);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:projects,slug,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $project->title = $request->title;
        $project->slug = Str::slug($request->slug);
        $project->short_desc = $request->short_desc;
        $project->content = $request->content;
        $project->construction_type = $request->construction_type;
        $project->sector = $request->sector;
        $project->location = $request->location;
        $project->status = $request->status;
        $project->save();


        if ($request->imageId > 0) {
            $oldImage = $project->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $exArr = explode('.', $tempImage->name);
                $ext = last($exArr);
                $filename = strtotime('now') . $project->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $destPath = public_path('uploads/projects/small/' . $filename);
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $image = $manager->read($sourcePath);
                $image->coverDown(500, 600);
                $image->save($destPath);

                // Create large thumbnail
                $destPath = public_path('uploads/projects/large/' . $filename);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200);
                $image->save($destPath);

                $project->image = $filename;
                $project->save();

                if ($oldImage!='') {
                # code...
                File::delete(public_path('uploads/projects/large/' . $oldImage));
                File::delete(public_path('uploads/projects/small/' . $oldImage));
            }

            }
        }

        return response()->json(['status' => true, 'message' => 'Project updated successfully'], 200);
    }
    
    public function destroy($id)
    {
        $project = Project::find($id);
        if ($project==null) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        File::delete(public_path('uploads/projects/large/' . $project->image));
        File::delete(public_path('uploads/projects/small/' . $project->image));
        $project->delete();
        return response()->json(['status' => true, 'message' => 'Project deleted successfully'], 200);
    }

    public function show($id)
    {
        //
        $project = Project::find($id);
        if ($project==null) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $project], 200);
    }
}
