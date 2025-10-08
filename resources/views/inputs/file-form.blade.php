<div class="p-5 text-white bg-gray-700 my-3 text-center">
    <div class="card-body" id="{{ $id }}">
        @php $column = $input->column; $value = $input->resource?->object?->$column; @endphp
        @if($value)
            <a href="{{ $value }}" target="_blank" class="text-base mb-4 block hover:underline">{{ __('See Current file') }}</a>
        @endif
        <p class="file-name"></p>
        <button type="button" class="bg-primary-600 hover:bg-primary-700 px-4 py-2 rounded-lg cursor-pointer border border-gray-400" onclick="executeFile('{{ $id }}')">{{ __('Upload File') }}</button>
        {{ html()->hidden($input->column, $input->value) }}
        {{ html()->file($input->column . '_new')->style('display:none;') }}
    </div>
</div>

@pushonce(config('front.scripts_stack'))
    <script>
        function executeFile(id) {
            $('#' + id + ' > input[type="file"]').click();
        };
    </script>
@endpushonce
@push(config('front.scripts_stack'))
    <script>
        $('#{{ $id }} > input[type="file"]').change(function(e) {
            var fileName = e.target.files[0].name;
            fileName = "<span class='pb-2 block'>"+fileName+"</span>";
            $('#{{ $id }} .file-name').html(fileName);
        });
    </script>
@endpush
