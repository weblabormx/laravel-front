<div class="p-5 text-white bg-gray-700 my-3 text-center">
    <div class="card-body" id="{{ $id }}">
        @php $column = $input->column; @endphp
        @if (isset($input->resource) && isset($input->resource->object) && isset($input->resource->object->$column))
            <img src="{{ \Front::thumbs()->get($input->resource->object->$column, $input->view_size) }}" class="mw-100 mx-auto"><br /><br />
        @elseif(isset($input->value))
            <img src="{{ $input->value }}" class="mw-100"><br /><br />
        @endif
        <p class="file-name"></p>
        <button type="button" class="btn btn-secondary" onclick="executeFile('{{ $id }}')">{{ __('Upload Image') }}</button>
        {{ html()->hidden($input->column, $input->value) }}
        {{ html()->file($input->column . '_new')->style('display:none;') }}
    </div>
</div>

@pushonce('scripts-footer')
    <script>
        function executeFile(id) {
            $('#' + id + ' > input[type="file"]').click();
        };
    </script>
@endpushonce
@push('scripts-footer')
    <script>
        $('#{{ $id }} > input[type="file"]').change(function(e) {
            var fileName = e.target.files[0].name;
            $('#{{ $id }} .file-name').html(fileName);
        });
    </script>
@endpush
