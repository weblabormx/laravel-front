@if($errors->any())
    <div class="alert alert-danger">
        Next errors were gotten: {{ collect($errors->all())->implode(', ')}}
    </div>
@endif