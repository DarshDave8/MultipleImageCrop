<?php

namespace App\Http\Controllers;

use App\Models\MultiImageCrop;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;



class MultiImageCropController extends Controller
{


    protected $OriginalImagePath = null;
    protected $ThumbImagePath = null;
    protected $ThumbImageHeight = null;
    protected $ThumbImageWidth = null;


    public function __construct()
    {
        $this->OriginalImagePath = Config::get('constant.IMAGE_ORIGINAL_PHOTO_UPLOAD_PATH');
        $this->ThumbImagePath = Config::get('constant.IMAGE_THUMB_PHOTO_UPLOAD_PATH');
        $this->ThumbImageHeight = Config::get('constant.IMAGE_THUMB_PHOTO_HEIGHT');
        $this->ThumbImageWidth = Config::get('constant.IMAGE_THUMB_PHOTO_WIDTH');
    }
    public function index(){
        $data = Photo::all();
        return view('multipleImages')->with('data', $data);
    }

    public function add()
    {
        return view('multi-image-crop');
    }

    public function store(Request $request)
    {
        $validateImageData = $request->validate([
            'images' => 'required',
            'images.*' => 'mimes:jpg,png,jpeg,gif,svg'
        ]);
        // Upload new images
        if ($request->hasfile('images')) {
            foreach ($request->file('images') as $key => $file) {
                try {
                    $params = [
                        'originalPath' => $this->OriginalImagePath,
                        'thumbPath' => $this->ThumbImagePath,
                        'thumbHeight' => $this->ThumbImageHeight,
                        'thumbWidth' => $this->ThumbImageWidth,
                    ];
                    if (!empty($file) && !empty($params)) {
                        $extension = $file->getClientOriginalExtension();
                        $name = Str::random(20) . '.' . $extension;
                        $storage = Storage::disk('public');
                        // Make original path
                        if (!$storage->exists($params['originalPath'])) {
                            $storage->makeDirectory($params['originalPath']);
                        }

                        // Make thumb path
                        if (!$storage->exists($params['thumbPath'])) {
                            $storage->makeDirectory($params['thumbPath']);
                        }

                        $originalPath = $params['originalPath'] . $name;
                        $thumbPath = $params['thumbPath'] . $name;
                        // Store original image
                        $storage->put($originalPath, file_get_contents($file), 'public');
                        if (Image::make($file)->height() > Image::make($file)->width()) {
                            $thumb = Image::make($file)->resize(null, $params['thumbHeight'], function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode($extension);
                        } else {
                            $thumb = Image::make($file)->resize($params['thumbWidth'], null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode($extension);
                        }

                        $storage->put($thumbPath, (string) $thumb, 'public');

                        $mutliImage = MultiImageCrop::create([
                            'name' => $name,
                            'path' => $originalPath,
                        ]);
                        $mutliImage->save();
                    }
                } catch (\Exception $e) {
                    Log::error(
                        $e->getMessage()
                    );
                    return false;
                }
            }
        }

        return redirect('multi-image-crop')->with('status', 'All Images has been uploaded successfully');
    }
}
