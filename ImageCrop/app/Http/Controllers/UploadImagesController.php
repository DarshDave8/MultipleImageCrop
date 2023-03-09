<?php

namespace App\Http\Controllers;

use App\Models\CroppedImage;
use Illuminate\Http\Request;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class UploadImagesController extends Controller
{
    public function index()
    {
        $data = Photo::all();
        return view('images-upload-form')->with('data',$data);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'image.*' => 'image|max:5096',
        ]);

        $data = [];
        if ($request->hasFile('image')) {
            // Process each uploaded image
            Storage::makeDirectory('public/Photo/original');
            Storage::makeDirectory('public/Photo/thumb');
            foreach ($request->file('image') as $key => $file) {
                try {
                    // Generate a unique filename
                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                    // Save the original image to disk
                    $originalPath = 'public/Photo/original/' . $filename;
                    $success = $file->storeAs('public/Photo/original', $filename);

                    if ($success) {
                        // Create a new Photo model and save it to the database
                        $photo = new Photo();
                        $photo->path = $originalPath;
                        $photo->title = $filename;
                        $photo->save();
                        DB::commit();
                        // Optimize the image (optional)
                        // Intervention\Image\Facades\Image::make($file)->save(storage_path('app/public/Photo/original/'.$filename), 75);
                        // $data[] = $photo;
                        array_push($data, $photo);
                    } else {
                        throw new \Exception('Failed to save image to disk');
                    }

                    // Save the thumbnail image if available
                    if (isset($request->croppedImages[$key])) {
                        // Decode the base64 image
                        $base64_str = $request->croppedImages[$key];
                        $image = str_replace('data:image/png;base64,', '', $base64_str);
                        $image = str_replace(' ', '+', $image);
                        $decodedImage = base64_decode($image);

                        // Save the thumbnail image to disk
                        $thumbnailPath = 'Photo/thumb/' . $filename;
                        $success = Storage::disk('public')->put($thumbnailPath, $decodedImage);

                        if ($success) {
                            // Create a new Photo model and save it to the database
                            $thumbnail = new Photo();
                            $thumbnail->path = $thumbnailPath;
                            $thumbnail->title = $filename;
                            $thumbnail->save();
                            DB::commit();
                            // Optimize the image (optional)
                            // Intervention\Image\Facades\Image::make($decodedImage)->save(storage_path('app/public/Photo/thumb/'.$thumbnailFilename), 75);
                        } else {
                            throw new \Exception('Failed to save thumbnail image to disk');
                        }
                    }
                } catch (\Exception $e) {
                    // Handle errors
                    DB::rollback();
                    Log::error('Error saving image: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Error saving image: ' . $e->getMessage());
                }
            }
        }
        $view = view('images-edit-form')->with('data', $data)->render();
        $result = [
            "data" => $data,
            "view" => $view
        ];
        return response()->json(['success' => 'Images uploaded successfully.', 'result' => $result]);
    }


    public function edit()
    {
        $data = Photo::all();
        return view('images-edit-form')->with('data', $data);
    }


    public  function update(Request $request)
    {
    }


    // public function cropAndSave(Request $request)
    // {
    //     $base64_str = $request->input('image');
    //     $image = str_replace('data:image/png;base64,', '', $base64_str);
    //     $image = str_replace(' ', '+', $image);
    //     $decodedImage = base64_decode($image);
    //     $filename = uniqid() . '.png';
    //     Storage::disk('public')->put('cropImages/' . $filename, $decodedImage);
    //     $imagePath = 'storage/images/' . $filename;
    //     $CroppedImage = new CroppedImage();
    //     $CroppedImage->path = $imagePath;
    //     $CroppedImage->title = $filename;
    //     $CroppedImage->save();


    //     // Redirect to the upload page
    //     return view('images-upload-form')->with('status', 'success');
    // }

    public function deleteFile(Request $request)
    {
        $fileUrl = $request->input('fileUrl');
        $filePath = public_path($fileUrl);

        if (file_exists($filePath)) {
            unlink($filePath);
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'File not found']);
        }
    }

    // public function edit()
    // {
    //     $data = Photo::all();
    //     return view('images-edit-form', $data);
    // }
}
