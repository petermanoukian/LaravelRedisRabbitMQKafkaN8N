@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Cats
            </div>
            <div class="card-body">
                <form id="bulkDeleteForm" action="{{ route('admin.cats.destroyMany') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <!-- Header checkbox -->
                                    <input type="checkbox" id="checkAll">
                                    <button type="button" id="inverseCheck" class="btn btn-sm btn-secondary ms-2">
                                        Inverse
                                    </button>
                                </th>
                               
                                <th>Name</th>
                                <th>File</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cats as $cat)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $cat->id }}" class="catCheckbox">
                                   {{ $cat->id }}
                                    </td>
                                    <td>{{ $cat->name }}</td>
                                    <td>
                                        @if(!empty($cat->filer))
                                            <a href="{{ asset('/' . $cat->filer) }}" target="_blank">
                                                {{ $cat->filename }} <br> {{ $cat->mime_label }} 
                                                <br> {{ $cat->size_label}}
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cats.edit', $cat->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            formmethod="POST"
                                            formaction="{{ route('admin.cats.destroy', $cat->id) }}"
                                            onclick="return confirm('Are you sure you want to delete this category?');">
                                            Delete
                                        </button>
                                        <hr>
                                        <a href="{{ route('admin.prods.index', ['catid' => $cat->id]) }}" class="btn btn-sm btn-info">
                                                View Products ({{ $cat->prods_count }})
                                        </a>
                                        <a href="{{ route('admin.prods.create', ['catid' => $cat->id]) }}" class="btn btn-sm btn-success">
                                            Add Product
                                        </a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-danger mt-2"
                        onclick="return confirm('Delete selected cats?');">
                        Delete Selected
                    </button>
                </form>
                
                <!-- Pagination links -->
                {{ $cats->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush
@include('includes.script.checkboxinverse')
