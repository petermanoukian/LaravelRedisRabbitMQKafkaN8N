@push('scripts')
<script>
    // Check/uncheck all
    document.getElementById('checkAll').addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.catCheckbox, .prodCheckbox').forEach(cb => cb.checked = checked);
    });

    // Inverse selection
    document.getElementById('inverseCheck').addEventListener('click', function() {
        document.querySelectorAll('.catCheckbox, .prodCheckbox').forEach(cb => cb.checked = !cb.checked);
    });
</script>
@endpush