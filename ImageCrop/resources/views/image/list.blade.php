@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Images</div>

                    <div class="card-body">
                        <div class="row">
                            @foreach ($images as $image)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->name }}" class="card-img-top">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $image->name }}</h5>
                                            <p class="card-text">{{ $image->description }}</p>
                                            <a href="{{ route('image.edit', $image->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <a href="{{ route('image.show', $image->id) }}" class="btn btn-secondary btn-sm">View</a>

                                            <form method="POST" action="{{ route('image.destroy', $image->id) }}" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
