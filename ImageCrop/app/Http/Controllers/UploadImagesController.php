<?php

namespace App\Http\Controllers;

use App\Models\CroppedImage;
use Illuminate\Http\Request;
use App\Models\Photo;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UploadImagesController extends Controller
{
    public function index()
    {
        $data = Photo::where('path', 'like', '%Photo/original%')->get();
        return view('images-upload-form')->with('data', $data);
    }

    // public function store(Request $request)
    // {
    //     try{
    //         $data = [];
    //         if ($request->hasFile('image')) {
    //             // Process each uploaded image
    //             Storage::makeDirectory('public/Photo/original');
    //             Storage::makeDirectory('public/Photo/thumb');
    //             Storage::makeDirectory('public/Photo/CroppedImage');
    //             foreach ($request->file('image') as $key => $file) {
    //                 try {
    //                     // Generate a unique filename
    //                     $filename = uniqid() . '.' . $file->getClientOriginalExtension();
    //                     $existingImage = Photo::where('id', $key)->where('path', 'like', '%Photo/original%')->first();
    //                     if ($existingImage) {
    //                         $filename = $existingImage->title;
    //                         array_push($data, $existingImage);
    //                     } else {
    //                         // Save the original image to disk
    //                         $originalPath = 'public/Photo/original/' . $filename;
    //                         $success = $file->storeAs('public/Photo/original', $filename);

    //                         if ($success) {
    //                             // Create a new Photo model and save it to the database
    //                             $photo = new Photo();
    //                             $photo->path = $originalPath;
    //                             $photo->title = $filename;
    //                             $photo->save();
    //                             DB::commit();
    //                             // Optimize the image (optional)
    //                             // Intervention\Image\Facades\Image::make($file)->save(storage_path('app/public/Photo/original/'.$filename), 75);
    //                             // $data[] = $photo;
    //                             array_push($data, $photo);
    //                         } else {
    //                             throw new \Exception('Failed to save image to disk');
    //                         }
    //                     }
    //                     // Save the thumbnail image if available
    //                     if (isset($request->croppedImages[$key])) {
    //                         // Decode the base64 image
    //                         $base64_str = $request->croppedImages[$key];
    //                         $image = str_replace('data:image/png;base64,', '', $base64_str);
    //                         $image = str_replace(' ', '+', $image);
    //                         $decodedImage = base64_decode($image);

    //                         // Save the cropped image to disk
    //                         $croppedImagePath = 'public/Photo/CroppedImage/' . $filename;
    //                         $success = Storage::put($croppedImagePath, $decodedImage);
    //                         $existingcroppedImage = Photo::where('id', $key + 1)->where('path', 'like', '%Photo/CroppedImage%')->first();

    //                         if ($existingcroppedImage) {
    //                             // Update the Photo model with the cropped image path
    //                             $photo->path = $croppedImagePath;
    //                             $photo->save();
    //                             DB::commit();

    //                             // Delete the existing thumbnail image if it exists
    //                             if ($existingcroppedImage) {
    //                                 Storage::delete($existingcroppedImage->path);
    //                                 $existingcroppedImage->delete();
    //                             }
    //                         } else {
    //                             $croppedImage = new Photo();
    //                             $croppedImage->path = $croppedImagePath;
    //                             $croppedImage->title = $filename;
    //                             $croppedImage->save();
    //                             DB::commit();
    //                         }
    //                     } else {
    //                         Image::make($file)->save(storage_path('app/public/Photo/thumb/' . $filename), 75);
    //                         $thumbImagePath = 'Photo/thumb/' . $filename;
    //                         $existingThumbnail = Photo::where('id', $key + 1)->where('path', 'like', '%Photo/thumb%')->first();

    //                         if ($existingThumbnail) {
    //                             $photo->path = $thumbImagePath;
    //                             $photo->save();
    //                             DB::commit();

    //                             // Delete the existing thumbnail image if it exists
    //                             if ($existingThumbnail) {
    //                                 Storage::delete($existingThumbnail->path);
    //                                 $existingThumbnail->delete();
    //                             }
    //                         } else {
    //                             // Create a new Photo model and save it to the database
    //                             $thumbnail = new Photo();
    //                             $thumbnail->path = $thumbImagePath;
    //                             $thumbnail->title = $filename;
    //                             $thumbnail->save();
    //                             DB::commit();
    //                         }
    //                     }
    //                 } catch (\Exception $e) {
    //                     // Handle errors
    //                     DB::rollback();
    //                     Log::error('Error saving image: ' . $e->getMessage());
    //                     return redirect()->back()->with('error', 'Error saving image: ' . $e->getMessage());
    //                 }
    //             }
    //         }
    //         $view = view('images-edit-form')->with('data', $data)->render();
    //         $result = [
    //             "data" => $data,
    //             "view" => $view
    //         ];
    //         return response()->json(['success' => 'Images uploaded successfully.', 'result' => $result]);
    //     } catch(\Exception $e) {
    //         dd($e->getMessage().'     '.$e->getLine());
    //     }
    // }


    public function store(Request $request)
    {
        try {
            $data = [];
            // dd($request->all());
            if ($request->image) {
                // Process each uploaded image
                Storage::makeDirectory('public/Photo/original');
                Storage::makeDirectory('public/Photo/thumb');
                Storage::makeDirectory('public/Photo/CroppedImage');
                foreach ($request->image as $key => $file) {
                    try {

                        // Generate a unique filename
                        $image_parts = explode(";base64,", $file); // separate the data and the MIME type
                        $image_type_aux = explode("image/", $image_parts[0]); // get the image type
                        $image_type = $image_type_aux[1];
                        $filename = uniqid() . '.' . $image_type;
                        $existingImage = Photo::where('id', $key)->where('path', 'like', '%Photo/original%')->first();

                        $image_data = str_replace('data:image/', '', $file); // Remove the "data:image/" prefix
                        list($extension, $image_data) = explode(';', $image_data); // Extract the file extension
                        list(, $image_data)      = explode(',', $image_data); // Remove the "data:image/*;base64," prefix
                        $decodedoriginalImage = base64_decode($image_data);

                        if ($existingImage) {
                            array_push($data, $existingImage);
                        } else {
                            // Save the original image to disk
                            $originalPath = 'public/Photo/original/' . $filename;
                            $success = Storage::put($originalPath, $decodedoriginalImage);

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
                        }
                        // Save the thumbnail image if available
                        // Check if cropped image is provided
                        if (isset($request->croppedImages[$key])) {
                            // Decode the base64-encoded cropped image
                            $cropped_image_data = $request->croppedImages[$key];
                            $cropped_image_parts = explode(";base64,", $cropped_image_data);
                            $cropped_image_type = str_replace("data:image/", "", $cropped_image_parts[0]);
                            $cropped_image_data = base64_decode($cropped_image_parts[1]);

                            // Save the cropped image to disk
                            $cropped_image_path = 'public/Photo/CroppedImage/' . $filename;
                            $success = Storage::put($cropped_image_path, $cropped_image_data);

                            // Delete the existing thumbnail image if it exists
                            $existing_thumbnail_image = Photo::where('id', $key + 1)->where('path', 'like', '%Photo/thumb%')->first();
                            if ($existing_thumbnail_image) {
                                Storage::delete($existing_thumbnail_image->path);
                                $existing_thumbnail_image->delete();
                                $photo->path = $cropped_image_path;
                                $photo->save();
                            } else {
                                $image = new Photo();
                                $image->path = $cropped_image_path;
                                $image->title = $filename;
                                $image->save();
                            }

                            // Update the Photo model with the cropped image path
                        } else {
                            // Create thumbnail image from the original image
                            $file_content = Image::make($file)->save(storage_path('app/public/Photo/thumb/' . $filename), 75);
                            $thumbnail_image_path = 'Photo/thumb/' . $filename;

                            // Save the thumbnail image to disk
                            $success = Storage::put('public/' . $thumbnail_image_path, $file_content);

                            // Delete the existing cropped image if it exists
                            $existing_cropped_image = Photo::where('id', $key + 1)->where('path', 'like', '%Photo/CroppedImage%')->first();
                            if ($existing_cropped_image) {

                            } else {
                                $image = new Photo();
                                $image->path = $thumbnail_image_path;
                                $image->title = $filename;
                                $image->save();
                            }
                        }

                        // Create a new Photo model and save it to the database
                        // if (!$existing_thumbnail_image && !$existing_cropped_image) {
                        //     $image = new Photo();
                        //     $image->path = $photo->path;
                        //     $image->title = $filename;
                        //     $image->save();
                        // }
                    } catch (\Exception $e) {
                        // Handle errors
                        DB::rollback();
                        Log::error('Error saving image: ' . $e->getMessage() . '     ' . $e->getLine());
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
        } catch (\Exception $e) {
            dd($e->getMessage() . '     ' . $e->getLine());
        }
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

    public function deleteFile(Request $request, $id)
    {
        $image = Photo::find($id);
        $thumbOrCropImage = Photo::find($id + 1);

        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found']);
        }

        try {
            Storage::delete($image->path); // delete the image file from storage
            $image->delete(); // delete the record from the database
            Storage::delete($thumbOrCropImage->path); // delete the image file from storage
            $thumbOrCropImage->delete(); // delete the record from the database
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete image']);
        }

        return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
    }
}
