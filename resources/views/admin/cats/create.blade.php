@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Create Category
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
                <form action="{{ route('admin.cats.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Name (required) --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control" value="{{ old('name') }}" required>
                    </div>

                    {{-- Description (optional, WYSIWYG) --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">Short Details</label>
                        <textarea name="des" id="des" class="form-control" required>{{ old('des') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="long_description" class="form-label">Long Description</label>
                        <textarea class="form-control dess" name="dess" placeholder="Additional Info"></textarea>
                    </div>


                    {{-- File (optional, allowed types incl. HTML) --}}
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
                    <button type="submit" class="btn btn-success">Save Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush

@include('includes.script.wisywig')