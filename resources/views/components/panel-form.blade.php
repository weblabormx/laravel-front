<div class="mt-6 md:grid md:grid-cols-3 md:gap-6">
  @if(isset($panel->title) && strlen($panel->title) > 0)
    <div class="md:col-span-1">
      <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium leading-6 text-gray-900">@lang($panel->title ?: 'Basic Information')</h3>
        <p class="mt-1 text-sm text-gray-600">{{$panel->description}}</p>
      </div>
    </div>
    <div class="mt-5 md:col-span-2 md:mt-0">
  @else
    <div class="mt-5 md:col-span-3 md:mt-0">
  @endif
        <div class="shadow sm:overflow-hidden sm:rounded-md">
          <div class="flex flex-col gap-6 px-4 py-5 bg-white md:grid md:grid-cols-12 sm:p-6">
            @foreach($panel->fields()->where('needs_to_be_on_panel', true) as $field)
                {!! $field->formHtml() !!}
            @endforeach
          </div>
        </div>
  @if(isset($panel->title) && strlen($panel->title) > 0)
    <div class="md:col-span-1">
    </div>
  </div>
  @else
    </div>
  @endif

  <div class="hidden sm:block" aria-hidden="true">
    <div class="py-5">
      <div class="border-t border-gray-200"></div>
    </div>
  </div>
</div>