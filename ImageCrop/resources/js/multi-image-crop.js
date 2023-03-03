var imgUpload = document.getElementById('upload-img')
    , imgPreview = document.getElementById('img-preview')
    , imgUploadForm = document.getElementById('form-upload')
    , totalFiles
    , previewTitle
    , previewTitleText
    , img;

imgUpload.addEventListener('change', previewImgs, true);

function previewImgs(event) {
    totalFiles = imgUpload.files.length;

    if (!!totalFiles) {
        imgPreview.classList.remove('img-thumbs-hidden');
    }

    for (var i = 0; i < totalFiles; i++) {
        wrapper = document.createElement('div');
        wrapper.classList.add('wrapper-thumb');
        removeBtn = document.createElement("span");
        cropBtn = document.createElement("span");
        nodeRemove = document.createTextNode('x');
        nodeCrop = document.createTextNode('c');
        removeBtn.classList.add('remove-btn');
        cropBtn.classList.add('crop-btn');
        removeBtn.appendChild(nodeRemove);
        cropBtn.appendChild(nodeCrop);
        img = document.createElement('img');
        img.src = URL.createObjectURL(event.target.files[i]);
        img.classList.add('img-preview-thumb');
        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        wrapper.appendChild(cropBtn);
        imgPreview.appendChild(wrapper);



        $('.remove-btn').click(function () {
            $(this).parent('.wrapper-thumb').remove();
        });
        var canvas = $('<canvas>').addClass('d-none');
        $('.modal-body').append(img).append(canvas);
        $('.crop-btn').click(function () {
            var cropper = new Cropper(img[0], {
                aspectRatio: 1,
                viewMode: 1,
                crop: function (event) {
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
            $('.saveBtn').on('click', function () {
                var croppedImage = canvas[0].toDataURL();
                console.log(croppedImage);
                $.ajax({
                    url: '/crop-image',
                    method: 'POST',
                    data: {
                        image: croppedImage,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        console.log(
                            'Image saved successfully.');
                    },
                    error: function (xhr, status, error) {
                        console.log(
                            'Error occurred while saving the image.'
                        );
                        console.log(error);
                    }
                });
                cropBtn.removeClass('d-none');
            });
        });

    }
}

imgUploadForm.on('submit', function (event) {

    var formData = new FormData();
    for (var i = 0; i < imagesToSubmit.length; i++) {
        formData.append('images[]', imagesToSubmit[i]);
    }

    $.ajax({
        url: '/multi-image-crop',
        type: 'POST',
        data: formData,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log(response);
        },
        error: function (xhr, textStatus, errorThrown) {
            console.error(errorThrown);
        }
    });

    event.preventDefault();
});
