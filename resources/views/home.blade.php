@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <div class="container py-4">
                <h2 class="mb-7">Products</h2>
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-12 d-flex">
                            <select id="category" name="category" class="form-control form-control-sm me-2 flex-grow-1" required>
                                @foreach($categories as $category)
                                    <option id="selected" value="{{ $category->id }}" {{ request()->segment(2)==$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                
                    <!-- عرض المنتجات المرتبطة -->
                <div class="row">
                    @forelse ($products as $product)
                        <div class="col-md-4 mt-4">
                            <div class="card h-100 shadow-sm">
                                <img src="{{ asset('./storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text description-clipped" id="desc-{{ $product->id }}">
                                        {{ Str::words($product->description, 30, '') }}
                                    </p>
                                    <div class="mt-auto">
                                        <p class="fw-bold">price: {{ $product->price }} $</p>
                                        <p class="text-muted">category:
                                            {{ $product->categories->pluck('name')->implode(' , ') }}
                                        </p>
                                        <a href="{{route('products.show',$product->id)}}" class="btn btn-primary w-100">More Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center">There is no products related to this category at the moment</p>
                    @endforelse
                </div>
            </div>
            
            <style>
                .description-clipped {
                    overflow: hidden;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                }
            </style>
            <script>
                document.getElementById('category').addEventListener('change', function() {
                    let category = this.value;
                    if (category) {
                            location.replace("{{ config('app.url') }}/home/"+category);
                    }
                });
            </script>

        </div>
    </div>
</div>
@endsection
