<ol class="breadcrumb bg-transparent">
    <li class="breadcrumb-item">
        <a href="/admin">Home</a>
    </li>
	@isset($front)
	    @foreach($front->getBreadcrumbs(isset($object) ? $object : null) as $breadcrumb)
	        <li class="breadcrumb-item @isset($breadcrumb['active']) active @endisset">
	        	@isset($breadcrumb['url'])
		        	<a href="{{$breadcrumb['url']}}">
		        @endisset
		        	{{$breadcrumb['title']}}
		        @isset($breadcrumb['url'])
		        	</a>
		        @endisset
		    </li>
	    @endforeach
	@else
		{{$slot}}
    @endisset
</ol>