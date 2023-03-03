


$(function (e) {



    var image = document.getElementById('cropImg');
    var cropBoxData;
    var canvasData;
    var cropper;
    var croppedCanvas
    var croppedImages = [];


    $(document).on('click', '.crop-btn', function (e) {
        console.log('image', image);
        var img = $(this).next('img')[0];
        $('#cropImg').attr('src', img.src);
        $('#cropperModal').modal('show');
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
            },
        });
    }).on('hidden.bs.modal', function () {
        cropBoxData = cropper.getCropBoxData();
        canvasData = cropper.getCanvasData();
        cropper.destroy();
    });
    $('.saveBtn').on('click', function (e) {
        var croppedImage = croppedCanvas.toDataURL();
        croppedImages.push(croppedImage);
        console.log(croppedImage);
        // $('#croppedImages').val(croppedImages);

        // $.ajax({
        //     url: '/crop-image',
        //     method: 'POST',
        //     data: {
        //         image: croppedImage,
        //     },
        //     headers: {
        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     },
        //     success: function (response) {
        //         console.log('Image saved successfully.');
        //     },
        //     error: function (xhr, status, error) {
        //         console.log(
        //             'Error occurred while saving the image.'
        //         );
        //         console.log(error);
        //     }
        // });
    });

    $('#myForm').on('submit', function (event) {
        // Add images to form data
        var formData = new FormData();
        for (var i = 0; i < croppedImages.length; i++) {
            formData.append('croppedImages[]', croppedImages[i]);
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
