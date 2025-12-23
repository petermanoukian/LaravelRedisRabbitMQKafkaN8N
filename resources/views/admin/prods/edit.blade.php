@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                Edit Product
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
                <form action="{{ route('admin.prods.update', $prod->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Category --}}
                    <div class="mb-3">
                        <label for="catid" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="catid" id="catid" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach ($cats as $cat)
                                <option value="{{ $cat->id }}" {{ $prod->catid == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control" value="{{ old('name', $prod->name) }}" required>
                    </div>

                    {{-- Short details --}}
                    <div class="mb-3">
                        <label for="des" class="form-label">Short Details</label>
                        <textarea name="des" id="des" class="form-control" required>{{ old('des', $prod->des) }}</textarea>
                    </div>

                    {{-- Long description --}}
                    <div class="mb-3">
                        <label for="dess" class="form-label">Long Description</label>
                        <textarea class="form-control dess" name="dess" placeholder="Additional Info">{{ old('dess', $prod->dess) }}</textarea>
                    </div>

                    {{-- Existing file --}}
                    @if(!empty($prod->filer))
                        <div class="mb-3">
                            <label class="form-label">Current File</label><br>
                            <a href="{{ asset('/' . $prod->filer) }}" target="_blank">
                                {{ $prod->filename }} <br>
                                {{ $prod->mime }} <br>
                                {{ $prod->sizer }} bytes <br>
                                {{ $prod->extension }}
                            </a>
                        </div>
                    @endif

                    {{-- File upload --}}
                    <div class="mb-3">
                        <label for="file" class="form-label">Replace File</label>
                        <input type="file" name="filer" id="file" class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.tiff,
                                    .pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,
                                    .txt,.csv,.json,.html,.htm">
                        <small class="text-muted">
                            Optional. Upload to replace existing file.
                        </small>
                    </div>

                    {{-- Existing image --}}
                    @if(!empty($prod->img2))
                        <div class="mb-3">
                            <label class="form-label">Current Image</label><br>
                            <img src="{{ asset('/' . $prod->img2) }}" alt="thumb" style="max-width:120px;">
                            <br>
                            <a href="{{ asset('/' . $prod->img) }}" target="_blank">View full image</a>
                        </div>
                    @endif

                    {{-- Image upload --}}
                    <div class="mb-3">
                        <label for="img" class="form-label">Replace Product Image</label>
                        <input type="file" name="img" id="img" class="form-control"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.tiff">
                        <small class="text-muted">
                            Optional. Upload to replace existing image.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@endpush

@include('includes.script.wisywig')
