<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    public function index()
    {
        $images = Post::all();

        return view('image.list', compact('images'));
    }


    public function create()
    {
        return view('image.create');
    }

    public function store(Request $request)
    {
        // Validate the image upload
        $validatedData = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Get the uploaded image file
        $imageFile = $request->file('image');

        // Generate a unique filename
        $filename = uniqid() . '.' . $imageFile->getClientOriginalExtension();

        // Store the original image
        $imageFile->storeAs('public/images', $filename);

        // Get the crop coordinates from the request
        $cropX = $request->input('crop_x');
        $cropY = $request->input('crop_y');
        $cropWidth = $request->input('crop_width');
        $cropHeight = $request->input('crop_height');

        // Open the original image
        $image = Image::make(storage_path('app/public/images/' . $filename));

        // Crop the image using the specified coordinates
        $croppedImage = $image->crop($cropWidth, $cropHeight, $cropX, $cropY);

        // Generate a unique filename for the cropped image
        $croppedFilename = uniqid() . '.' . $imageFile->getClientOriginalExtension();

        // Store the cropped image
        $croppedImage->save(storage_path('app/public/images/' . $croppedFilename));

        // Save the image data to the database
        $imageData = [
            'filename' => $croppedFilename,
            'original_filename' => $filename,
            'width' => $croppedImage->getWidth(),
            'height' => $croppedImage->getHeight(),
        ];

        $image = Image::create($imageData);

        // Redirect to the image list page
        return redirect()->route('image.index');
    }

    public function edit($id)
    {
        $image = Image::findOrFail($id);

        return view('image.edit', compact('image'));
    }

    public function update(Request $request, $id)
    {
        // Validate the updated image data
        $validatedData = $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Get the image to update
        $image = Image::findOrFail($id);

        // Update the image data if the user uploaded a new image
        if ($request->hasFile('image')) {
            // Get the uploaded image file
            $imageFile = $request->file('image');

            // Generate a unique filename
            $filename = uniqid() . '.' . $imageFile->getClientOriginalExtension();

            // Store the original image
            $imageFile->storeAs('public/images', $filename);

            // Get the crop coordinates from the request
            $cropX = $request->input('crop_x');
            $cropY = $request->input('crop_y');
            $cropWidth = $request->input('crop_width');
            $cropHeight = $request->input('crop_height');

            // Open the original image
            $imageData = Image::make(storage_path('app/public/images/' . $filename))->toArray();

            // Crop the image using the specified coordinates
            $croppedImage = Image::make(storage_path('app/public/images/' . $filename))->crop($cropWidth, $cropHeight, $cropX, $cropY);

            // Generate a unique filename for the cropped image
            $croppedFilename = uniqid() . '.' . $imageFile->getClientOriginalExtension();

            // Store the cropped image
            $croppedImage->save(storage_path('app/public/images/' . $croppedFilename));

            // Delete the old image files
            Storage::delete([
                'public/images/' . $image->filename,
                'public/images/' . $image->original_filename,
            ]);

            // Update the image data with the new information
            $imageData['filename'] = $croppedFilename;
            $imageData['original_filename'] = $filename;

            $image->update($imageData);
        }

        // Redirect to the image list page
        return redirect()->route('image.index');
    }

    public function destroy($id)
    {
        // Get the image to delete
        $image = Image::findOrFail($id);

        // Delete the image files from storage
        Storage::delete([
            'public/images/' . $image->filename,
            'public/images/' . $image->original_filename,
        ]);

        // Delete the image data from the database
        $image->delete();

        // Redirect to the image list page
        return redirect()->route('image.index');
    }
}
