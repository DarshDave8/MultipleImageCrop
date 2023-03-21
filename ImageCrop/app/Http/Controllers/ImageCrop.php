<?php

namespace App\Http\Controllers;

use App\Models\CropImage;
use App\Models\ImageCropModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class ImageCrop extends Controller
{

    protected $OriginalImagePath = null;
    protected $ThumbImagePath = null;
    protected $CropImagePath = null;

    public function __construct()
    {
        $this->OriginalImagePath = Config::get('constant.IMAGE_ORIGINAL_PHOTO_UPLOAD_PATH');
        $this->ThumbImagePath = Config::get('constant.IMAGE_THUMB_PHOTO_UPLOAD_PATH');
        $this->CropImagePath = Config::get('constant.IMAGE_CROP_PHOTO_UPLOAD_PATH');
    }

    public function decodeBase64($base64Data, $destinationPath, $filename, $existingName = NULL)
    {
        $image_parts = explode(";base64,", $base64Data);
        $image_type_aux = explode("image/", $image_parts[0]);
        $extension = $image_type_aux[1];
        $image_data = str_replace('data:image/', '', $base64Data);
        list($extension, $image_data) = explode(';', $image_data);
        list(, $image_data)      = explode(',', $image_data);
        $decodedData = base64_decode($image_data);
        if ($existingName != NULL) {
            $path = $destinationPath . '/' . $existingName;
            $name = $filename . '.' . $extension;
        } else {
            $path = $destinationPath . '/' . $filename . '.' . $extension;
            $name = $filename . '.' . $extension;
        }
        Storage::put($path, $decodedData);

        return [
            'name' => $name,
            'type' => $extension,
            'path' => $path,
            'existingName' => !empty($existingName) ? $existingName : NULL
        ];
    }

    public function list()
    {
        $data = CropImage::all();
        return view('image-crop')->with('data', $data);
    }

    public function store(Request $request)
    {
        $data = [];
        Storage::makeDirectory('public/image/original');
        Storage::makeDirectory('public/image/thumb');
        Storage::makeDirectory('public/image/crop');
        try {
            if ($request->image) {
                // dd($request->all());
                $params = [
                    'originalPath' => $this->OriginalImagePath,
                    'thumbPath' => $this->ThumbImagePath,
                    'cropPath' => $this->CropImagePath,
                ];
                foreach ($request->image as $key => $originalImage) {
                    $filename = uniqid();
                    $original = $filename . '_original';
                    $originalImageName = $this->decodeBase64($originalImage, $params['originalPath'], $original);
                    $thumbExist = CropImage::where('id', $key)->where('thumb_name', 'like', '%_thumb%')->first();
                    $cropExist = CropImage::where('id', $key)->where('crop_name', 'like', '%_crop%')->first();

                    if (!empty($request->croppedImages[$key])) {
                        if (!empty($cropExist)) {
                            $cropImageName = $cropExist->crop_name;
                            Storage::delete('public/' . $params['cropPath'] . '/' . $cropImageName);
                            $cropImage = $request->croppedImages[$key];
                            $crop = $filename . '_crop';
                            $croppedImageName = $this->decodeBase64($cropImage, $params['cropPath'], $crop, $cropImageName);

                            $image = CropImage::find($key);
                            $image->crop_name = !empty($croppedImageName['name']) ? $croppedImageName['name'] : NULL;
                            $image->thumb_name = NULL;
                            $image->save();

                            array_push($data, $image);
                        } else {

                            $cropImage = $request->croppedImages[$key];
                            $crop = $filename . '_crop';
                            $croppedImageName = $this->decodeBase64($cropImage, $params['cropPath'], $crop);
                            if (!empty($thumbExist)) {
                                $ImageThumb = $thumbExist->thumb_name;
                                Storage::delete('public/' . $params['thumbPath'] . '/' . $ImageThumb);
                            }
                        }
                    } else {
                        if (!empty($thumbExist)) {
                            $image = CropImage::find($key);
                            array_push($data, $image);
                        } else {
                            $file_content = Image::make($originalImage)->save(storage_path('app/public/Photo/thumb/' . $filename), 75);
                            $thumbName = $filename . '_thumb';
                            $thumbImageName = $thumbName  . ' . ' .  $originalImageName['type'];
                            $thumbnail_image_path = $params['thumbPath'] . $thumbImageName;
                            $success = Storage::put('public/' . $thumbnail_image_path, $file_content);
                        }
                    }
                    if (empty($cropExist) && empty($thumbExist)) {
                        // $data[] = CropImage::create([
                        //     'original_name' => !empty(trim($originalImageName['name'])) ? trim($originalImageName['name']) : NULL,
                        //     'file_name' => !empty($originalImageName['name']) ? $originalImageName['name'] : NULL,
                        //     'crop_name' => !empty($croppedImageName['name']) ? $croppedImageName['name'] : NULL,
                        //     'thumb_name' => !empty($thumbImageName) ? $thumbImageName : NULL,
                        //     'file_type' => !empty($originalImageName['type']) ? $originalImageName['type'] : NULL,
                        //     'isvideo' => 0,
                        // ]);
                        $image = new CropImage();
                        $image->original_name = !empty($originalImageName['name']) ? $originalImageName['name'] : NULL;
                        $image->file_name = !empty($originalImageName['name']) ? $originalImageName['name'] : NULL;
                        $image->crop_name = !empty($croppedImageName['name']) ? $croppedImageName['name'] : NULL;
                        $image->thumb_name = empty($croppedImageName['name']) ? $thumbImageName : NULL;
                        $image->file_type = !empty($originalImageName['type']) ? $originalImageName['type'] : NULL;
                        $image->isvideo = 0;
                        $image->save();
                        DB::commit();
                        array_push($data, $image);
                    } elseif (empty($cropExist) && !empty($thumbExist)) {
                        // $data[] = CropImage::where('id', $key)->update([
                        //     'original_name' => !empty(trim($originalImageName['name'])) ? trim($originalImageName['name']) : NULL,
                        //     'file_name' => !empty($originalImageName['name']) ? $originalImageName['name'] : NULL,
                        //     'crop_name' => !empty($croppedImageName['existingName']) ? $croppedImageName['existingName'] : NULL,
                        //     'thumb_name' => !empty($thumbImageName) ? $thumbImageName : NULL,
                        //     'file_type' => !empty($originalImageName['type']) ? $originalImageName['type'] : NULL,
                        //     'isvideo' => 0,
                        // ]);
                        $image = CropImage::find($key);
                        $image->original_name = !empty($originalImageName['name']) ? $originalImageName['name'] : NULL;
                        $image->file_name = !empty($originalImageName['name']) ? $originalImageName['name'] : NULL;
                        $image->crop_name = !empty($croppedImageName['name']) ? $croppedImageName['name'] : NULL;
                        $image->thumb_name = NULL;
                        $image->file_type = !empty($originalImageName['type']) ? $originalImageName['type'] : NULL;
                        $image->isvideo = 0;
                        $image->save();
                        DB::commit();
                        array_push($data, $image);
                    }
                }
            }
            $view = view('image-crop-edit')->with('data', $data)->render();
            $result = [
                "data" => !empty($data) ? $data : NULL,
                "view" => $view
            ];
            return response()->json(['success' => 'Images uploaded successfully.', 'result' => $result]);
        } catch (\Exception $e) {

            Log::info("This is ImageCrop store route.");
            Log::error(config('constant.DEFAULT_ERROR_MESSAGE'), [
                '<Message>' => $e->getMessage() . '  ' . $e->getLine(),
            ]);
        }
    }

    public function deleteFile(Request $request, $id)
    {
        $image = CropImage::find($id);

        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found']);
        }

        try {
            Storage::disk('public')->delete([
                $this->OriginalImagePath . '/' . $image->original_name,
                $this->ThumbImagePath . '/' . $image->crop_name,
                $this->CropImagePath . '/' . $image->thumb_name,
            ]);

            $image->delete(); // delete the record from the database
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete image']);
        }

        return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
    }
}
