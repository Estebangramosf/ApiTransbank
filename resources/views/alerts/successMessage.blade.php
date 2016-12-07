@if(Session::has('message'))
	<div id="msj-success" class="alert alert-success alert-dismissible" role="fadeIn">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		{!!Session::get('message')!!}
	</div>
@endif