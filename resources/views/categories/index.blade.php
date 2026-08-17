@extends('layouts.app')
@section('topbar_title', 'All Categories')
@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="page-title mb-1">All Categories</h3>
        <p class="page-subtitle">{{ $categories->count() }} categor{{ $categories->count() == 1 ? 'y' : 'ies' }} in total.</p>
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3">
        <i class="ti ti-circle-plus fs-5"></i> Add Category
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table dash-table align-middle">
                <thead><tr>
                    <th>#</th><th>Name</th><th>Products</th><th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="text-muted">{{ $category->id }}</td>
                        <td class="fw-semibold">
                            <span class="d-inline-flex align-items-center gap-2">
                                <span class="stat-icon soft-info" style="width:34px;height:34px;font-size:16px;"><i class="ti ti-category"></i></span>
                                {{ $category->name }}
                            </span>
                        </td>
                        <td><span class="badge badge-pill badge-soft-muted">{{ $category->products_count }} product{{ $category->products_count == 1 ? '' : 's' }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this category? Products will be detached from it.');" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete"><i class="ti ti-trash"></i><span>Delete</span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No categories yet. <a href="{{ route('categories.create') }}">Add your first category</a>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
