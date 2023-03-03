@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Edit Image</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('image.update', $image->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label for="image" class="col-md-4 col-form-label text-md-right">Image</label>

                                <div class="col-md-6">
                                    <input id="image" type="file" class="form-control @error('image') is-invalid @enderror" name="image" required autofocus>

                                    @error('image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="crop" class="col-md-4 col-form-label text-md-right">Crop</label>

                                <div class="col-md-6">
                                    <img id="image-preview" src="{{ asset('storage/images/' . $image->filename) }}" alt="Image Preview">

                                    <input type="hidden" id="crop-x" name="crop_x">
                                    <input type="hidden" id="crop-y" name="crop_y">
                                    <input type="hidden" id="crop-width" name="crop_width">
                                    <input type="hidden" id="crop-height" name="crop_height">
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Save Changes
                                    </button>
                                    <a href="{{ route('image.index') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var image = document.getElementById('image-preview');
            var cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 3,
                dragMode: 'move',
                autoCropArea: 1,
                cropBoxResizable: false,
                cropBoxMovable: false,
                minCropBoxWidth: 200,
                minCropBoxHeight: 200,
                ready: function () {
                    cropper.setCropBoxData({ width: 200, height: 200 });
                },
                crop: function (event) {
                    document.getElementById('crop-x').value = event.detail.x;
                    document.getElementById('crop-y').value = event.detail.y;
                    document.getElementById('crop-width').value = event.detail.width;
                    document.getElementById('crop-height').value = event.detail.height;
                }
            });
        });
    </script>
@endpush
