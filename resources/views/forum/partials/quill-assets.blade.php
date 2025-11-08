@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
        <style>
            .forum-quill-editor .ql-editor {
                min-height: 160px;
                max-height: 400px;
                overflow-y: auto;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    @endpush
@endonce

