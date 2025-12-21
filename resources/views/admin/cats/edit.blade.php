@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Edit Category
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
                <form action="{{ route('admin.cats.update', $cat->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control" value="{{ old('name', $cat->name) }}" required>
                    </div>

                    {{-- Short Details --}}
                    <div class="mb-3">
                        <label for="des" class="form-label">Short Details</label>
                        <textarea name="des" id="des" class="form-control" required>{{ old('des', $cat->des) }}</textarea>
                    </div>

                    {{-- Long Description --}}
                    <div class="mb-3">
                        <label for="dess" class="form-label">Long Description</label>
                        <textarea class="form-control dess" name="dess" placeholder="Additional Info">{{ old('dess', $cat->dess) }}</textarea>
                    </div>

                    {{-- File --}}
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload File</label>
                        @if(!empty($cat->filer))
                            <div class="mb-2">
                                <a href="{{ asset('/' . $cat->filer) }}" target="_blank">Current File</a>
                            </div>
                        @endif
                        <input type="file" name="filer" id="file" class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.tiff,
                                    .pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,
                                    .txt,.csv,.json,.html,.htm">
                        <small class="text-muted">
                            Optional. Upload a new file to replace the existing one.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success">Update Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush
@include('includes.script.wisywig')