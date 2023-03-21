$(function (e) {
    var UplodedImage = [];
    var formData = new FormData($('#myForm')[0]);
    $(document).ready(function () {
        let images = $('.images-preview-div img[id^="image-"]');
        images.each(function () {
            let id = $(this).data('id');
            let src = $(this).attr('src');
            console.log(`Processing image ${id} with src ${src}`);
            let isHidden = $(this).is(':hidden');
            if (isHidden) {
                let img = new Image();
                img.src = src;
                img.onload = function () {
                    let canvas = document.createElement('canvas');
                    canvas.width = this.width;
                    canvas.height = this.height;
                    let ctx = canvas.getContext('2d');
                    ctx.drawImage(this, 0, 0);
                    let base64String = canvas.toDataURL('image/png');
                    console.log(`Image ${id} is hidden, base64 string is ${base64String}`);
                    formData.append(`image[${id}]`, base64String);
                };
            } else {
                fetch(src)
                    .then(response => response.blob())
                    .then(blob => {
                        const reader = new FileReader();
                        reader.readAsDataURL(blob);
                        reader.onloadend = () => {
                            let base64String = reader.result;
                            // console.log(`Image ${id} is not hidden, base64 string is ${base64String}`);
                            formData.append(`image[${id}]`, base64String);
                        };
                    })
                    .catch(error => console.error(error));
            }
        });
    });
    const getIndex = () => {
        const indexArr = [...$('.image-container .img-fluid')].map(ele =>
            parseInt($(ele).attr('data-id'))
        );
        const totalFiles = UplodedImage.length + indexArr.length;
        for (let i = 1; i <= totalFiles + 1; i++) {
            if (!indexArr.includes(i)) {
                return i;
            }
        }
        return 1;
    };

    const previewImages = async (input, imgPreviewPlaceholder) => {
        const filesAmount = input.files.length;
        const filePromises = [];
        for (let i = 0; i < filesAmount; i++) {
            const reader = new FileReader();
            reader.fileIndex = i;
            filePromises.push(
                new Promise((resolve, reject) => {
                    reader.onload = () => {
                        const base64String = reader.result.split(',')[1];
                        const fileIndex = reader.fileIndex;
                        const index = getIndex();
                        const currentCounter = index;
                        const imageId = `image-${currentCounter}`;
                        const deleteBtnId = `delete-btn-${currentCounter}`;
                        const cropBtnId = `crop-btn-${currentCounter}`;
                        const imageContainerId = `image-container${currentCounter}`;
                        const imageContainer = `
            <div class="col-md-4 image-container" id="${imageContainerId}">
              <button type="button" class="btn btn-danger delete-btn mt-1" id="${deleteBtnId}">Delete</button>
              <button type="button" class="btn btn-secondary crop-btn mt-1" id="${cropBtnId}">Crop</button>
              <img class="img-fluid" src="${reader.result}" id="${imageId}" data-id="${currentCounter}">
            </div>`;
                        $(imgPreviewPlaceholder).append(imageContainer);
                        UplodedImage.push({ id: imageId, data: reader.result });
                        formData.append(`image[${currentCounter}]`, reader.result);
                        resolve();
                    };
                    reader.readAsDataURL(input.files[i]);
                })
            );
        }
        await Promise.all(filePromises);
    };

    $('#images').on('change', function () {
        previewImages(this, 'div.images-preview-div');
    });

    $(document).on('click', '.delete-btn', e => {
        const id = e.currentTarget.id.split('-')[2]; // get the ID of the clicked delete button
        const imageContainer = $(`#image-${id}`).parent();
        const imageIndex = UplodedImage.findIndex(img => img.id === `image-${id}`);
        if (imageIndex !== -1) {
            UplodedImage.splice(imageIndex, 1); // remove the image from the array at the corresponding index
            formData.delete(`image[${id}]`);
        }
        const croppedImageContainer = $(`#croppedImages${id}`);
        if (croppedImageContainer.length) {
            croppedImageContainer.remove();
            formData.delete(`croppedImages[${id}]`);
        }

        $.ajax({
            url: `/delete-crop-image/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .done(function (response) {
                imageContainer.fadeOut(500, function () {
                    $(this).remove();
                });
                console.log(response.message);
            })
            .fail(function (error) {
                console.log(error.responseJSON.message);
            });

    });

    $(document).on('click', '.crop-btn', (event) => {
        const { id } = event.currentTarget;
        const imageId = id.split('-')[2];
        const imgElement = $(event.currentTarget).next('img')[0];
        const cropImgElement = $('#cropImg');
        console.log(cropImgElement, imgElement);
        const imageSrc = imgElement.src;
        cropImgElement.attr('src', imageSrc);
        $('#cropperModal').modal('show');
        $('#image-id').val(imageId);
    });

    const cropperModal = $('#cropperModal');
    const preview = '.preview';
    let cropper;
    let croppedCanvas;
    let croppedImageData;
    let cropBoxData;
    let canvasData;

    cropperModal.on('shown.bs.modal', () => {
        const image = document.getElementById('cropImg');
        cropper = new Cropper(image, {
            autoCropArea: 0.5,
            aspectRatio: 1 / 1,
            viewMode: 1,
            scalable: false,
            zoomable: false,
            background: false,
            preview,
            crop: () => {
                //Should set crop box data first here
                croppedCanvas = cropper.getCroppedCanvas();
                croppedImageData = croppedCanvas.toDataURL();
            },
        });
    }).on('hidden.bs.modal', () => {
        if (cropper) {
            cropBoxData = cropper.getCropBoxData();
            canvasData = cropper.getCanvasData();
            cropper.destroy();
        }
    }).on('shown.bs.modal', () => {
        if (!cropper) {
            console.error('Cropper is not initialized.');
        }
    }).on('hide.bs.modal', () => {
        if (croppedImageData) {
            console.log('Cropped image data:', croppedImageData);
        } else {
            console.error('No cropped image data.');
        }
    });

    const updatePreviewImage = (id, imageData) => {
        const img = document.getElementById(`image-${id}`);
        img.setAttribute('hidden', true);
        let crImg = document.getElementById(`cr-${id}`);
        if (crImg) {
            crImg.src = imageData;
        } else {
            crImg = $('<img>').attr({
                id: `cr-${id}`,
                name: `dummyImage[${id}]`,
                src: imageData,
            });
        }
        const containerId = `image-container${id}`;
        $(`#${containerId}`).append(crImg);
    };

    $('.saveBtn').on('click', (e) => {
        const id = $('#image-id').val();
        const cropInput = $('<input>').attr({
            type: 'hidden',
            name: `croppedImages[${id}]`,
            value: croppedCanvas.toDataURL(),
            id: `croppedImages${id}`,
        }).data('id', id);
        const Crid = $(`#croppedImages${id}`);
        if (Crid) {
            Crid.remove();
        }
        $('#myForm').append(cropInput);
        const cropId = $('#croppedImages' + id).val();
        formData.append(`croppedImages[${id}]`, cropId);
        updatePreviewImage(id, croppedImageData);
    });

    $('#myForm').on('submit', (event) => {
        const images = document.querySelectorAll('.images-preview-div img');
        $.ajax({
            url: '/ImageUpdate',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        }).done((res) => {
            console.log(res);
            let parsedResponse = JSON.parse(res);
            let view = parsedResponse.result.view;
            let viewData = res.result.view;
            console.log(parsedResponse);
            formData.empty();
            // var container = $('.images-preview-div');
            // container.empty();
            $('.images-preview-div').html(viewData);
            // formData = new FormData();
            let responseData = res.result.data;
            let uniqueDataArray = []; // Array to store unique data
            responseData.forEach((item) => {
                // Check if the id already exists in uniqueDataArray
                let existingItem = uniqueDataArray.find((uniqueItem) => uniqueItem.id === item.id);
                if (!existingItem) {
                    uniqueDataArray.push(item);
                }
            });
            uniqueDataArray.forEach((item) => {
                let image = $(`#image-${item.id}`)[0]; // Get the DOM element
                let imageUrl = $(`#image-${item.id}`).attr('src'); // Get the image src
                console.log(imageUrl);
                fetch(imageUrl)
                    .then(response => response.blob()) // Convert response to blob
                    .then(blob => {
                        // Convert blob to base64 string
                        const reader = new FileReader();
                        reader.readAsDataURL(blob);
                        reader.onloadend = () => {
                            let base64String = reader.result;
                            console.log(base64String);
                            formData.append(`image[${item.id}]`, base64String); // Append the data to the formData object
                        };
                    })
                    .catch(error => console.error(error));
            });
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error(`${textStatus}: ${errorThrown}`);
        });

        event.preventDefault();

    });

});
