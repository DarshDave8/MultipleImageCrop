$(function (e) {

    var image = document.getElementById('cropImg');
    var cropBoxData;
    var canvasData;
    var cropper;
    var croppedCanvas
    var cropInput = [];
    var i = 0;
    // var croppedImageData;
    var UplodedImage = [];
    var imagesArr = [];
    var formData = new FormData($('#myForm')[0]);

    // var previewImages = function (input, imgPreviewPlaceholder) {
    //     if (input.files) {
    //         var filesAmount = input.files.length;
    //         for (i = 0; i < filesAmount; i++) {
    //             var reader = new FileReader();
    //             reader.onload = function (event) {
    //                 var counter = 1;
    //                 var imageContainer = '<div class="col-md-4 image-container"><button type="button" class="btn btn-danger delete-btn mt-1" id="delete-btn-' + counter + '">Delete</button><button type="button" class="btn btn-secondary crop-btn mt-1" id="crop-btn-' + counter + '">Crop</button><img class="img-fluid" src="' + event.target.result + '" id="image-' + counter + '"></div>';
    //                 $(imgPreviewPlaceholder).append(imageContainer);
    //                 UplodedImage.push(event.target.result);
    //                 console.log(counter);
    //                 counter++
    //             }
    //             reader.readAsDataURL(input.files[i]);
    //         }
    //         console.log(UplodedImage);
    //     }
    // };

    function getIndex() {
        var indexArr = [];
        var oldImageLen = UplodedImage.length;
        var elementLen = $('.image-container .img-fluid').length;
        $.each($('.image-container .img-fluid'), function (key, ele) {
            indexArr.push(parseInt($(ele).attr('data-id')));
        });
        var totalFiles = (oldImageLen + elementLen);
        for (var i = 1; i <= totalFiles + 1; i++) {
            if (!indexArr.includes(i)) {
                return i;
            }
        }
        return 1;
    }

    var previewImages = function (input, imgPreviewPlaceholder) {
        console.log('Old Js');
        if (input.files) {
            var filesAmount = input.files.length;
            var counter = 1; // Define counter outside the loop
            for (i = 0; i < filesAmount; i++) {
                var reader = new FileReader();
                reader.fileIndex = i;
                // Use a closure to pass the counter to onload
                reader.onload = (function (e) {
                    return function (event) {
                        var fileIndex = event.target.fileIndex;
                        var index = getIndex();
                        currentCounter = index;
                        var imageId = 'image-' + currentCounter;
                        var deleteBtnId = 'delete-btn-' + currentCounter;
                        var cropBtnId = 'crop-btn-' + currentCounter;
                        var imageContainerId = 'image-container' + currentCounter;
                        var imageContainer = `<div class="col-md-4 image-container" id="${imageContainerId}"><button type="button" class="btn btn-danger delete-btn mt-1" id="${deleteBtnId}">Delete</button><button type="button" class="btn btn-secondary crop-btn mt-1" id="${cropBtnId}">Crop</button><img class="img-fluid" src="${event.target.result}" id="${imageId}"  data-id = "${currentCounter}"></div>`;
                        $(imgPreviewPlaceholder).append(imageContainer);
                        // Store image data with ID in the UplodedImage array
                        UplodedImage.push({ id: imageId, data: event.target.result });

                        // console.log(UplodedImage[currentCounter-1]['data']);
                        formData.append('image[' + currentCounter + ']', input.files[fileIndex]);

                    };
                })(counter);
                reader.readAsDataURL(input.files[i]);
                counter++; // Increment counter after passing to onload
            }
            console.log(UplodedImage);
        }
    };

    $('#images').on('change', function () {
        previewImages(this, 'div.images-preview-div');

    });



    $(document).on('click', '.delete-btn', function (e) {
        var id = $(this).attr('id').split('-')[2]; // get the ID of the clicked delete button
        $('#image-' + id).parent().remove(); // remove the parent element of the image with the same ID
        UplodedImage.splice(id - 1, 1); // remove the image from the array at the corresponding index
        console.log(UplodedImage, id);
        var Crid = $('#croppedImages' + id);
        if (Crid != null) {
            formData.delete('croppedImages[' + id + ']');
        }
        var imageId = $('#image' + id);
        if (imageId != null) {
            $(imageId).remove();
            formData.delete('image[' + id + ']');
        }
    });





    $(document).on('click', '.crop-btn', function (e) {
        console.log('image', image);
        var img = $(this).next('img')[0];
        $('#cropImg').attr('src', img.src);
        $('#cropperModal').modal('show');
        var id = $(this).attr('id').split('-')[2];
        $('#image-id').val(id);
    })





    $('#cropperModal').on('shown.bs.modal', function () {
        console.log('modal shown');
        cropper = new Cropper(image, {


            autoCropArea: 0.5,
            aspectRatio: 1 / 1,
            viewMode: 1,
            scalable: false,
            zoomable: false,
            background: false,
            preview: '.preview',
            crop: function () {
                //Should set crop box data first here
                croppedCanvas = cropper.getCroppedCanvas();
                croppedImageData = croppedCanvas.toDataURL();
            },
        });
    }).on('hidden.bs.modal', function () {
        cropBoxData = cropper.getCropBoxData();
        canvasData = cropper.getCanvasData();
        cropper.destroy();
    });




    function updatePreviewImage(id, imageData) {
        var img = document.getElementById('image-' + id);
        img.setAttribute('hidden', true);
        var crImg = document.getElementById('cr-' + id);
        if (crImg != null) {
            var img = document.getElementById('cr-' + id);
            img.src = imageData;
        } else {
            cropedInput = $('<img>').attr({
                id: 'cr-' + id,
                name: 'dummyImage[' + id + ']',
                src: imageData,
            });
        }
        var containerId = 'image-container' + id
        $('#' + containerId).append(cropedInput)


    }


    $('.saveBtn').on('click', function (e) {
        var id = $('#image-id').val();
        cropInput = $('<input>').attr({
            type: 'hidden',
            name: 'croppedImages[' + id + ']',
            value: croppedCanvas.toDataURL(),
            id: 'croppedImages' + id,
        }).data('id', id);
        var Crid = $('#croppedImages' + id);
        if (Crid != null) {
            $(Crid).remove();
        }
        $("#myForm").append(cropInput);
        var cropId = $('#croppedImages' + id).val();
        formData.append('croppedImages[' + id + ']', cropId);
        updatePreviewImage(id, croppedImageData)

    });







    $('#myForm').on('submit', function (event) {

        // Submit form data to controller
        $.ajax({
            url: '/upload-multiple-image-preview',
            type: 'POST',
            data: formData,
            // enctype: 'multipart/form-data',
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log(response);
            },
            error: function (errorThrown) {
                console.error(errorThrown);
            }
        });

        event.preventDefault();
    });



});
