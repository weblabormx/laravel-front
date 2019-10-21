<table class="table table-striped bg-white" style="margin-top: 30px;">
    <thead class="thead-dark">
        <tr>
        	@foreach($table->headers as $header)
            	<th>{!! $header !!}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($table->data as $column)
            <tr>
            	@foreach($column as $value)
                	<td>{!! $value !!}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody> 
</table>