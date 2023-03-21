<html>

<head>
    <title> Multiple Image Upload </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}">

</head>

<body>
    <div class="container mt-5">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <div class="card">
            <div class="card-header text-center font-weight-bold">
                <h2> Multiple Image Upload </h2>
            </div>
            <div class="card-body">
                <form name="images-upload-form" id="myForm" method="POST" action="" accept-charset="utf-8"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="file" name="images[]" id="images" placeholder="Choose images"
                                    multiple>
                            </div>
                            @error('images')
                                <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <div class="mt-1 text-center">
                                <div class="row images-preview-div">
                                    @foreach ($data as $image)
                                        @if (!is_null($image->original_name))
                                            @if (!is_null($image->crop_name))
                                                <div class="col-md-4 image-container"
                                                    id="image-container{{ $image->id }}">
                                                    <button type="button" class="btn btn-danger delete-btn mt-1"
                                                        id="delete-btn-{{ $image->id }}">Delete</button>
                                                    <button type="button" class="btn btn-secondary crop-btn mt-1"
                                                        id="crop-btn-{{ $image->id }}">Crop</button>
                                                    <img class="img-fluid"
                                                        src="{{ url('storage/image/original/' . $image->original_name) }}"
                                                        alt="user-avatar" class="d-block rounded"
                                                        id="image-{{ $image->id }}" data-id="{{ $image->id }}"
                                                        hidden />
                                                    <img class="img-fluid"
                                                        src="{{ url('storage/image/crop/' . $image->crop_name) }}"
                                                        alt="user-avatar" class="d-block rounded"
                                                        id="dummyImage{{ $image->id }}"
                                                        name="dummyImage{{ $image->id }}" data-id="{{ $image->id }}" />
                                                    <input type="hidden" name="cropped[{{ $image->id }}]"
                                                        id="cropped{{ $image->id }}"
                                                        value="{{ $image->crop_name }}">
                                                </div>
                                            @else
                                                <div class="col-md-4 image-container"
                                                    id="image-container{{ $image->id }}">
                                                    <button type="button" class="btn btn-danger delete-btn mt-1"
                                                        id="delete-btn-{{ $image->id }}">Delete</button>
                                                    <button type="button" class="btn btn-secondary crop-btn mt-1"
                                                        id="crop-btn-{{ $image->id }}">Crop</button>
                                                    <img class="img-fluid"
                                                        src="{{ url('storage/image/original/' . $image->original_name) }}"
                                                        alt="user-avatar" class="d-block rounded"
                                                        id="image-{{ $image->id }}" data-id="{{ $image->id }}"/>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary" id="submit">Submit</button>
                        </div>
                    </div>
                    {{-- <input type="hidden" name="cropped_images"> --}}
                </form>
                <div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <input type="hidden" value="" id="image-id">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel">Cropper</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="img-container">
                                    <img id="cropImg" src="" alt="Picture">
                                </div>
                                <div class="preview_wrapper">
                                    <div class="preview" id="cropedImg"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary saveBtn" data-dismiss="modal">Save
                                    changes</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.js"
    integrity="sha512-LjPH94gotDTvKhoxqvR5xR2Nur8vO5RKelQmG52jlZo7SwI5WLYwDInPn1n8H9tR0zYqTqfNxWszUEy93cHHwg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://unpkg.com/bootstrap@4/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script src="{{ URL::asset('js/ImageCrop.js') }}"></script>

</html>
