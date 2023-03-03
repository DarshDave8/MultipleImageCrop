@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ $image->name }}</div>

                    <div class="card-body">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->name }}">

                        <form method="POST" action="{{ route('image.destroy', $image->id) }}">
                            @csrf
                            @method('DELETE')

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-danger">
                                        Delete
                                    </button>
                                    <a href="{{ route('image.index') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
