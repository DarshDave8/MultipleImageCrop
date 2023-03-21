<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'file_name',
        'crop_name',
        'thumb_name',
        'file_type',
        'isvideo'
    ];
}
