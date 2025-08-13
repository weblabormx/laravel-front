<div class="mt-4">
  @if(isset($panel->title) && strlen($panel->title) > 0)
    <h3 class="text-lg font-medium leading-6 text-gray-900">@lang($panel->title ?: 'Basic Information')</h3>
    <p class="mt-1 text-sm text-gray-600">{{$panel->description}}</p>
  @endif
  <div class="shadow sm:overflow-hidden sm:rounded-md pt-1">
    <div class="flex flex-col gap-6 px-4 py-5 bg-white md:grid md:grid-cols-12 sm:p-6">
      @foreach($panel->fields()->where('needs_to_be_on_panel', true) as $field)
          {!! $field->formHtml() !!}
      @endforeach
    </div>
  </div>
  <div class="pt-4">
    <div class="border-t border-gray-200"></div>
  </div>
</div>