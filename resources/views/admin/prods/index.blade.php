@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                Products
            </div>
            <div class="card-body">
                <!-- Filter by category -->
                <form method="POST" action="{{ route('admin.prods.index') }}" class="mb-3">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <select name="catid" class="form-select" onchange="this.form.submit()">
                                <option value="">-- All Categories --</option>
                                @foreach($cats as $cat)
                                    <option value="{{ $cat->id }}" {{ $cat->selected ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                <form id="bulkDeleteForm" action="{{ route('admin.prods.destroyMany') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="checkAll">
                                    <button type="button" id="inverseCheck" class="btn btn-sm btn-secondary ms-2">
                                        Inverse
                                    </button>
                                </th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>File</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prods as $prod)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $prod->id }}" class="prodCheckbox">
                                    </td>
                                    <td>{{ $prod->id }}</td>
                                    <td>{{ $prod->name }}</td>
                                    <td>{{ $prod->cat->name ?? '—' }}</td>
                                    <td>
                                        @if(!empty($prod->filer))
                                            <a href="{{ asset('/' . $prod->filer) }}" target="_blank">
                                                {{ $prod->filename }} <br>
                                                {{ $prod->mime }} <br>
                                                {{ $prod->sizer }} bytes <br>
                                                {{ $prod->extension }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($prod->img))
                                            <img src="{{ asset('/' . $prod->img2) }}" alt="thumb" style="max-width:80px;">
                                            <br>
                                            <a href="{{ asset('/' . $prod->img) }}" target="_blank">View full image</a>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.prods.edit', $prod->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            formmethod="POST"
                                            formaction="{{ route('admin.prods.destroy', $prod->id) }}"
                                            onclick="return confirm('Delete this product?');">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-danger mt-2"
                        onclick="return confirm('Delete selected products?');">
                        Delete Selected
                    </button>
                </form>

                <!-- Pagination links -->
                {{ $prods->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush
@include('includes.script.checkboxinverse')

