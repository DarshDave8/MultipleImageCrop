<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ URL::asset('css/multiImageCrop.css') }}" />
    {{-- <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}"> --}}
</head>

<body>
    <div class="container my-5">
        <h3 class="text-center">Multiple Upload Images and Remove Button </h3>
        <div class="row">
            <div class="col">
                <form action="" method="post" enctype="multipart/form-data" id="form-upload">
                    @csrf
                    <div class="form-group mt-5">
                        <label for="">Choose Images</label>
                        <input type="file" class="form-control" name="images[]" multiple id="upload-img" />
                    </div>
                    <div class="img-thumbs img-thumbs-hidden" id="img-preview"></div>
                    <button type="submit" class="btn btn-dark">Upload</button>
                </form>
            </div>
        </div>

    </div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('js/multi-image-crop.js') }}"></script>
{{-- <script src="{{ URL::asset('js/custom.js') }}"></script> --}}

</html>
