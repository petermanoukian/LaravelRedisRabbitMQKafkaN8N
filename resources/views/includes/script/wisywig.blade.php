@push('scripts')

<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('.dess'))
        .then(editor => {
            editor.ui.view.editable.element.style.minHeight = "200px";
        })
        .catch(error => {
            console.error(error);
        });
</script>


@endpush
