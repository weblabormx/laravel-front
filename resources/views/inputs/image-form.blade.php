<div class="p-5 text-white bg-gray-700 my-3 text-center">
    <div class="card-body" id="{{ $id }}">
        @php $column = $input->column; @endphp
        @if (isset($input->resource) && isset($input->resource->object) && isset($input->resource->object->$column))
            <img src="{{ \Front::thumbs()->get($input->resource->object->$column, $input->view_size) }}" class="mw-100 mx-auto mb-4">
        @elseif(isset($input->value))
            <img src="{{ $input->value }}" class="mw-100 mb-4">
        @endif
        <p class="file-name"></p>
        <button type="button" class="bg-primary-600 hover:bg-primary-700 px-4 py-2 rounded-lg cursor-pointer border border-gray-400" onclick="executeFile('{{ $id }}')">{{ __('Upload Image') }}</button>
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
            fileName = "<span class='pb-2 block'>"+fileName+"</span>";
            $('#{{ $id }} .file-name').html(fileName);
        });
    </script>
@endpush
