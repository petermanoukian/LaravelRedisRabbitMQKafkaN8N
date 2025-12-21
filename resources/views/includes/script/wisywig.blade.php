@push('scripts')
<script src="https://cdn.tiny.cloud/1/wtkr004h3tlah7yljg2m1o3rg03scnqq5lg4ph3jjhg7j59t/tinymce/8/tinymce.min.js" 
        referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
tinymce.init({
  selector: '.dess',
  menubar: false,
  toolbar: 'formatselect | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote | undo redo removeformat',
  branding: false,
  height: 300,
  width: '100%'
});
</script>
@endpush
