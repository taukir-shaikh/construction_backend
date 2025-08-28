<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\TempImage;
use File;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Validator;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::orderBy('created_at', 'DESC')->get();
        return response()->json(['status' => true, 'data' => $members], 200);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:members',
            'job_title' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'linkedin' => 'nullable|url',
            'status' => 'required|in:0,1'
        ]);

        if ($validation->fails()) {
            return response()->json(['status' => false, 'errors' => $validation->errors()], 422);
        }

        $member = new Member();
        $member->name = $request->name;
        $member->email = $request->email;
        $member->job_title = $request->job_title;
        $member->linkedin = $request->linkedin;
        $member->status = $request->status;
        $member->save();

        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $exArr = explode('.', $tempImage->name);
                $ext = last($exArr);
                $filename = strtotime('now') . $member->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $destPath = public_path('uploads/members/' . $filename);
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $image = $manager->read($sourcePath);
                $image->coverDown(400, 500);
                $image->save($destPath);

                $member->image = $filename;
                $member->save();

            }
        }

        return response()->json(['status' => true, 'message' => 'Member added successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $member = Member::find($id);
        if ($member == null) {
            return response()->json(['status' => false, 'message' => 'Member not found'], 404);
        }
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:members',
        ]);

        if ($validation->fails()) {
            return response()->json(['status' => false, 'errors' => $validation->errors()], 422);
        }

        $member->name = $request->name;
        $member->email = $request->email;
        $member->job_title = $request->job_title;
        $member->linkedin = $request->linkedin;
        $member->status = $request->status;
        $member->save();

        if ($request->imageId > 0) {
            $oldImage = $member->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $exArr = explode('.', $tempImage->name);
                $ext = last($exArr);
                $filename = strtotime('now') . $member->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $manager = new ImageManager(new Driver());
                $destPath = public_path('uploads/members/' . $filename);
                $image = $manager->read($sourcePath);
                $image->coverDown(400, 500);
                $image->save($destPath);


                $member->image = $filename;
                $member->save();

                if ($oldImage != '') {
                    # code...
                    File::delete(public_path('uploads/members/' . $oldImage));
                }

            }
        }

        return response()->json(['status' => true, 'message' => 'Member updated successfully'], 200);
    }

    public function show($id)
    {
        $member = Member::find($id);
        if ($member == null) {
            return response()->json(['status' => false, 'message' => 'Member not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $member], 200);
    }
    public function destroy($id)
    {
        $member = Member::find($id);
        if ($member == null) {
            return response()->json(['status' => false, 'message' => 'Member not found'], 404);
        }
        $member->delete();
        return response()->json(['status' => true, 'message' => 'Member deleted successfully'], 200);
    }
}
