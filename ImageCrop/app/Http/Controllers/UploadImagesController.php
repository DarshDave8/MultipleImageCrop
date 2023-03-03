<?php

namespace App\Http\Controllers;

use App\Models\CroppedImage;
use Illuminate\Http\Request;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class UploadImagesController extends Controller
{
    public function index()
    {
        return view('images-upload-form');
    }

    public function store(Request $request)
    {
        // $validateImageData = $request->validate([
        //     'images' => 'required',
        //     'images.*' => 'mimes:jpg,png,jpeg,gif,svg'
        // ]);

        $insert = [];
        dd($request->all());
        // Upload new images
        if ($request->hasfile('images')) {
            foreach ($request->file('images') as $key => $file) {
                if ($file) {
                    $path = $file->store('public/Photo/original');
                    $name = $file->getClientOriginalName();
                    $insert[$key]['title'] = $name;
                    $insert[$key]['path'] = $path;
                }
            }
        }
        // Insert new images
        Photo::insert($insert);
        if ($request->croppedImages) {
            foreach ($request->input('croppedImages') as $key => $file) {
                $base64_str = $file;
                $image = str_replace('data:image/png;base64,', '', $base64_str);
                $image = str_replace(' ', '+', $image);
                $decodedImage = base64_decode($image);
                $filename = uniqid() . '.png';
                Storage::disk('public')->put('/Photo/thumb' . $filename, $decodedImage);
                $imagePath = 'storage/Photo/thumb/' . $filename;
                $CroppedImage = new Photo();
                $CroppedImage->path = $imagePath;
                $CroppedImage->title = $filename;
                $CroppedImage->save();
            }
        }
        return redirect('upload-multiple-image-preview')->with('status', 'All Images has been uploaded successfully');
    }







    public function cropAndSave(Request $request)
    {
        $base64_str = $request->input('image');
        $image = str_replace('data:image/png;base64,', '', $base64_str);
        $image = str_replace(' ', '+', $image);
        $decodedImage = base64_decode($image);
        $filename = uniqid() . '.png';
        Storage::disk('public')->put('cropImages/' . $filename, $decodedImage);
        $imagePath = 'storage/images/' . $filename;
        $CroppedImage = new CroppedImage();
        $CroppedImage->path = $imagePath;
        $CroppedImage->title = $filename;
        $CroppedImage->save();


        // Redirect to the upload page
        return view('images-upload-form')->with('status', 'success');
    }

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
}
