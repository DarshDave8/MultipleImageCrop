@foreach ($data as $image)
    @if (!is_null($image->original_name))
        @if (file_exists(public_path() . '/storage/image/crop/' . $image->crop_name . ''))
            <div class="col-md-4 image-container" id="image-container{{ $image->id }}">
                <button type="button" class="btn btn-danger delete-btn mt-1"
                    id="delete-btn-{{ $image->id }}">Delete</button>
                <button type="button" class="btn btn-secondary crop-btn mt-1"
                    id="crop-btn-{{ $image->id }}">Crop</button>
                <img class="img-fluid" src="{{ url('storage/image/crop/' . $image->crop_name) }}" alt="user-avatar"
                    class="d-block rounded" id="dummyImage{{ $image->id }}" name="dummyImage{{ $image->id }}"
                    data-id="{{ $image->id }}" />
                <img class="img-fluid" src="{{ url('storage/image/original/' . $image->original_name) }}"
                    alt="user-avatar" class="d-block rounded" id="image-{{ $image->id }}"
                    data-id="{{ $image->id }}" hidden />
                <input type="hidden" name="cropped[{{ $image->id }}]" id="cropped{{ $image->id }}"
                    value="{{ $image->crop_name }}">
            </div>
        @else
            <div class="col-md-4 image-container" id="image-container{{ $image->id }}">
                <button type="button" class="btn btn-danger delete-btn mt-1"
                    id="delete-btn-{{ $image->id }}">Delete</button>
                <button type="button" class="btn btn-secondary crop-btn mt-1"
                    id="crop-btn-{{ $image->id }}">Crop</button>
                <img class="img-fluid" src="{{ url('storage/image/original/' . $image->original_name) }}"
                    alt="user-avatar" class="d-block rounded" id="image-{{ $image->id }}"
                    data-id="{{ $image->id }}" />
            </div>
        @endif
    @endif
@endforeach
