@extends('layouts/main')

@section('content')
<div class="container dashboard">

<ul class="podcast-list">
@foreach($podcasts as $podcast)
  <li><a href="{{ route('podcast', ['podcast'=>$podcast]) }}" class="btn btn-default">{{ $podcast->url }}</a></li>
@endforeach

  <li><a href="{{ route('new_podcast') }}" class="btn btn-primary btn-sm">New Podcast</a></li>
</ul>

</div>
@endsection
