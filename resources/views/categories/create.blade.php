@extends('layouts.app')
@section('topbar_title', 'Add Category')
@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="page-title mb-1">Add a New Category</h3>
        <p class="page-subtitle">Create a category to group your products.</p>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <i class="ti ti-arrow-left"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card form-card">
            <div class="card-body p-4">
                <form action="{{ route('categories.store') }}" method="post">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="form-label">Category Name</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Electronics">
                        <div class="form-text">Between 3 and 30 characters. Must be unique.</div>
                        @error('name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4">
                            <i class="ti ti-device-floppy"></i> Create Category
                        </button>
                        <a href="{{ route('categories.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
