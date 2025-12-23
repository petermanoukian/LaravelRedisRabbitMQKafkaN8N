@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Create Product
            </div>
            <div class="card-body">
                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('admin.prods.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="catid" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="catid" id="catid" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach ($cats as $cat)
                                <option value="{{ $cat->id }}" {{ $cat->selected ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control" value="{{ old('name') }}" required>
                    </div>

               


                   
                    <div class="mb-3">
                        <label for="des" class="form-label">Short Details</label>
                        <textarea name="des" id="des" class="form-control" required>{{ old('des') }}</textarea>
                    </div>

                    {{-- Long description --}}
                    <div class="mb-3">
                        <label for="dess" class="form-label">Long Description</label>
                        <textarea class="form-control dess" name="dess" placeholder="Additional Info">{{ old('dess') }}</textarea>
                    </div>

                    {{-- File upload --}}
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload File</label>
                        <input type="file" name="filer" id="file" class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.tiff,
                                    .pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,
                                    .txt,.csv,.json,.html,.htm">
                        <small class="text-muted">
                            Optional. Allowed: images, docs, spreadsheets, presentations, text, CSV, JSON, HTML.
                        </small>
                    </div>

                    {{-- Image upload --}}
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Product Image</label>
                        <input type="file" name="img" id="img" class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.tiff">
                        <small class="text-muted">
                            Optional. Large, small, and original versions will be generated.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success">Save Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@endpush

@include('includes.script.wisywig')
