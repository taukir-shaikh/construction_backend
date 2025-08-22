<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\TempImage;
use File;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Str;
use Validator;

class ArticleController extends Controller
{
    //this method will fetch all articles 
    public function index()
    {
        $aricles = Article::orderBy('created_at', 'DESC')->get();
        return response()->json(["status" => true, "data" => $aricles, "code" => 200]);
    }

    public function show($id)
    {
        $article = Article::find($id);
        if ($article == null) {
            # code...
            return response()->json(["status" => false, "message" => "Article not found", "code" => 404]);
        }
        return response()->json(["status" => true, "data" => $article, "code" => 200]);
    }
    // this method insert article in db
    public function store(Request $request)
    {
        $request->merge(['slug' => Str::slug($request->slug)]);
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required',
        ]);

        if ($validator->fails()) {
            # code...
            return response()->json(["status" => false, "data" => $validator->errors(), "code" => 400]);
        }

        $article = new Article();
        $article->title = $request->title;
        $article->slug = $request->slug;
        $article->author = $request->author;
        $article->content = $request->content;
        $article->status = $request->status;

        $article->save();

        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $exArr = explode('.', $tempImage->name);
                $ext = last($exArr);
                $filename = strtotime('now') . $article->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $destPath = public_path('uploads/articles/small/' . $filename);
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $image = $manager->read($sourcePath);
                $image->coverDown(500, 600);
                $image->save($destPath);

                // Create large thumbnail
                $destPath = public_path('uploads/articles/large/' . $filename);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200);
                $image->save($destPath);

                $article->image = $filename;
                $article->save();

            }
        }

        return response()->json(["status" => true, "data" => $article, "code" => 200]);
    }

    public function update($id, Request $request)
    {
        $article = Article::find($id);
        if ($article == null) {
            # code...
            return response()->json(["status" => false, "message" => "Article not found", "code" => 404]);
        }
        $request->merge(['slug' => Str::slug($request->slug)]);
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:articles,slug,' . $id . ',id',
        ]);

        if ($validator->fails()) {
            # code...
            return response()->json(["status" => false, "data" => $validator->errors(), "code" => 400]);
        }

        $article->title = $request->title;
        $article->slug = $request->slug;
        $article->author = $request->author;
        $article->content = $request->content;
        $article->status = $request->status;

        $article->save();

        if ($request->imageId > 0) {
            $oldImage = $project->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {
                $exArr = explode('.', $tempImage->name);
                $ext = last($exArr);
                $filename = strtotime('now') . $article->id . '.' . $ext;

                // ✅ Correct source path (inside /thumb/)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (!file_exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Temp image file not found: ' . $sourcePath
                    ], 404);
                }

                // Create image manager instance
                $destPath = public_path('uploads/articles/small/' . $filename);
                $manager = new ImageManager(new Driver());

                // Create small thumbnail
                $image = $manager->read($sourcePath);
                $image->coverDown(500, 600);
                $image->save($destPath);

                // Create large thumbnail
                $destPath = public_path('uploads/articles/large/' . $filename);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200);
                $image->save($destPath);

                $article->image = $filename;
                $article->save();

                if ($oldImage != '') {
                    # code...
                    File::delete(public_path('uploads/articles/large/' . $oldImage));
                    File::delete(public_path('uploads/articles/small/' . $oldImage));
                }

            }
        }

        return response()->json(["status" => true, 'message' => 'Article updated successfully', "data" => $article, "code" => 200]);
    }

    public function destroy($id)
    {
        $article = Article::find($id);
        if ($article == null) {
            # code...
            return response()->json(["status" => false, "message" => "Article not found", "code" => 404]);
        }
        if ($article->image != '') {
            # code...
            File::delete(public_path('uploads/articles/large/' . $article->image));
            File::delete(public_path('uploads/articles/small/' . $article->image));
        }
        $article->delete();
        return response()->json(["status" => true, "message" => "Article deleted successfully", "code" => 200]);
    }
}
