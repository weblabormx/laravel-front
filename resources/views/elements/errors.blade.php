@if($errors->any())
    <div class="text-sm text-red-700 rounded-md bg-red-50 p-4 my-4">
        {{ __('Next errors were gotten:') }} {{ collect($errors->all())->implode(', ')}}
    </div>
@endif