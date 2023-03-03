<!DOCTYPE html>
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
                <form name="images-upload-form" id="myForm" method="POST"
                    action="{{ url('upload-multiple-image-preview') }}" accept-charset="utf-8"
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
                                <div class="row images-preview-div"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary" id="submit">Submit</button>
                        </div>
                    </div>
                    {{-- <input type="hidden" name="cropped_images"> --}}
                    <input type="hidden" name="croppedImages[]" id="croppedImages" >


                </form>
                <div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel">Cropper</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="img-container">
                                    <img id="cropImg" src="../images/picture.jpg" alt="Picture">
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

            <style>
                .images-preview-div .col-md-4 {
                    margin-bottom: 20px;
                }

                .images-preview-div img {
                    max-width: 100%;
                    height: auto;
                }

                .images-preview-div button {
                    width: 100%;
                }

                .preview_wrapper {
                    border: 1px solid red;
                }

                .preview_wrapper,
                .preview {
                    overflow: hidden;
                    width: 300px;
                    height: 150px;
                }
            </style>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.js"
        integrity="sha512-LjPH94gotDTvKhoxqvR5xR2Nur8vO5RKelQmG52jlZo7SwI5WLYwDInPn1n8H9tR0zYqTqfNxWszUEy93cHHwg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/bootstrap@4/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script src="{{ URL::asset('js/custom.js') }}"></script>

    <script>
        $(function() {
            // Keep track of deleted images
            var deletedImages = [];
            // Multiple images preview with JavaScript
            var previewImages = function(input, imgPreviewPlaceholder) {
                if (input.files) {
                    var filesAmount = input.files.length;
                    for (i = 0; i < filesAmount; i++) {
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            var imageContainer =
                                '<div class="col-md-4"><button type="button" class="btn btn-secondary crop-btn mt-1" id="cropImage">Crop</button><img class="img-fluid" src="' +
                                event.target.result + '"></div>';
                            $(imgPreviewPlaceholder).append(imageContainer);

                            // Add delete button for each image


/*
                            // Add canvas element for each image
                            var canvas = $('<canvas>').addClass('d-none');
                            col.append(canvas);
                            $('.modal-body').append(img).append('<canvas>');
                            var cropBtn = $('<button>').addClass('btn btn-danger mt-1 crop-btn').text(
                                'Crop').attr('type', 'button');*/

                            /*cropBtn.on('click', function() {
                                $('#myModal').show();
                            col.append(canvas);
                                var cropper = new Cropper(img[0], {
                                    aspectRatio: 1,
                                    viewMode: 1,
                                    crop: function(event) {
                                        var croppedCanvas = cropper.getCroppedCanvas({
                                            width: 200,
                                            height: 200
                                        });
                                        canvas[0].width = croppedCanvas.width;
                                        canvas[0].height = croppedCanvas.height;
                                        canvas[0].getContext('2d').drawImage(
                                            croppedCanvas, 0, 0);
                                    }
                                });
                                cropBtn.addClass('d-none');
                                var saveBtn = $('<button>').addClass(
                                    'btn btn-primary mt-1 save-btn').text('Save').attr('type',
                                    'button');
                                    $('saveBtn').on('click', function() {
                                    var croppedImage = canvas[0].toDataURL();
                                    console.log(croppedImage);

                                    $.ajax({
                                        url: '/crop-image',
                                        method: 'POST',
                                        data: {
                                            image: croppedImage,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function(response) {
                                            console.log(
                                                'Image saved successfully.');
                                                $('#myModal').hide();
                                                var cropImage = $($.parseHTML('<img>')).addClass('img-fluid').attr('src', croppedImage);
                                                col.append(cropImage);
                                        },
                                        error: function(xhr, status, error) {
                                            console.log(
                                                'Error occurred while saving the image.'
                                            );
                                            console.log(error);
                                        }
                                    });
                                    cropBtn.removeClass('d-none');
                                    saveBtn.addClass('d-none');
                                });
                                col.append(saveBtn);
                            }); */

                            // Add crop button for each image
                            // col.append(cropBtn);
                        }
                        reader.readAsDataURL(input.files[i]);
                    }
                }
            };

            $('#images').on('change', function() {
                previewImages(this, 'div.images-preview-div');
            });
            $('#close').on('click', function() {
                $('#myModal').hide();
            })
            // Handle form submission
            // Handle form submission
            // $('#myForm').on('submit', function(event) {
            //     // Exclude deleted images
            //     console.log('Deleted images', deletedImages);
            //     var imagesToSubmit = [];
            //     $('.images-preview-div img').each(function() {
            //         if (!deletedImages.includes($(this).attr('src'))) {
            //             imagesToSubmit.push($(this).attr('src'));
            //         }
            //     });

            //     // Add images to form data
            //     var formData = new FormData();
            //     for (var i = 0; i < imagesToSubmit.length; i++) {
            //         formData.append('images[]', imagesToSubmit[i]);
            //     }
            //     // Submit form data to controller
            //     $.ajax({
            //         url: '/upload-multiple-image-preview',
            //         type: 'POST',
            //         data: formData,
            //         dataType: 'json',
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            //         success: function(response) {
            //             console.log(response);
            //         },
            //         error: function(xhr, textStatus, errorThrown) {
            //             console.error(errorThrown);
            //         }
            //     });

            //     event.preventDefault();
            // });


            // Send file to server for deletion
            // function deleteFile(fileUrl) {
            //     var formData = new FormData();
            //     formData.append('fileUrl', fileUrl);

            //     $.ajax({
            //         url: '/delete-file',
            //         type: 'POST',
            //         data: formData,
            //         dataType: 'json',
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            //         processData: false,
            //         contentType: false,
            //         success: function(response) {
            //             console.log(response);
            //         },
            //         error: function(xhr, textStatus, errorThrown) {
            //             console.error(errorThrown);
            //         }
            //     });
            // }


        });
    </script>

    </div>
</body>

</html>
