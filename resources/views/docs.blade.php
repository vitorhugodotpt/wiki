@extends('wiki::default')

@section('content')
<div>
	@include('wiki::partials.sidebar')

	<div class="documentation is-{{ config('wiki.ui.code_theme') }}" :class="{'expanded': ! sidebar}">
        {!! $content !!}
		@include('wiki::plugins.forum')
	</div>
</div>
@endsection
