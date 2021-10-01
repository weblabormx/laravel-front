@if($errors->any())
    <div class="alert alert-danger">
        {{ __('Next errors were gotten:') }} {{ collect($errors->all())->implode(', ')}}
    </div>
@endif