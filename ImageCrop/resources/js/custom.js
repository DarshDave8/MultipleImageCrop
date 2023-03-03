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
                    var img = $($.parseHTML('<img>')).addClass('img-fluid').attr('src', event.target
                        .result);
                    var col = $('<div>').addClass('col-md-4').append(img);
                    col.appendTo(imgPreviewPlaceholder);

                    // Add delete button for each image
                    var deleteBtn = $('<button>').addClass('btn btn-danger mt-1').text('Delete')
                        .attr('type', 'button');
                    deleteBtn.on('click', function() {
                        // Instead of removing the image, mark it for deletion
                        deletedImages.push(event.target.result);
                        col.hide();
                        $(this).siblings('input[type="hidden"]').val(event.target.result);
                    });
                    col.append(deleteBtn);
                    var deletedImageInput = $('<input>').attr('type', 'hidden')
                        .attr('name', 'deletedImages[]')
                        .val('');
                    col.append(deletedImageInput);

                    // Add canvas element for each image
                    var canvas = $('<canvas>').addClass('d-none');
                    col.append(canvas);
                    $('.modal-body').append(img).append('<canvas>');
                    var cropBtn = $('<button>').addClass('btn btn-danger mt-1 crop-btn').text(
                        'Crop').attr('type', 'button');

                    cropBtn.on('click', function() {
                        $('#myModal').show();
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
                            $('.saveBtn').on('click', function() {
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
                    });

                    // Add crop button for each image
                    col.append(cropBtn);
                }
                reader.readAsDataURL(input.files[i]);
            }
        }
    };

    $('#images').on('change', function() {
        previewImages(this, 'div.images-preview-div');
    });

    // Handle form submission
    // Handle form submission
    $('#myForm').on('submit', function(event) {
        // Exclude deleted images
        console.log('Deleted images', deletedImages);
        var imagesToSubmit = [];
        $('.images-preview-div img').each(function() {
            if (!deletedImages.includes($(this).attr('src'))) {
                imagesToSubmit.push($(this).attr('src'));
            }
        });

        // Add images to form data
        var formData = new FormData();
        for (var i = 0; i < imagesToSubmit.length; i++) {
            formData.append('images[]', imagesToSubmit[i]);
        }
        // Submit form data to controller
        $.ajax({
            url: '/upload-multiple-image-preview',
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error(errorThrown);
            }
        });

        event.preventDefault();
    });


    // Send file to server for deletion
    function deleteFile(fileUrl) {
        var formData = new FormData();
        formData.append('fileUrl', fileUrl);

        $.ajax({
            url: '/delete-file',
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error(errorThrown);
            }
        });
    }


});
