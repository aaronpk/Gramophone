@extends('layouts/main')

@section('content')
<div class="container">
  <h2>Add a Podcast</h2>

  @if(session('auth_error'))
    <div class="alert alert-danger">
      <strong>{{ session('auth_error') }}</strong> 
      <p>{{ session('auth_error_description') }}</p>
    </div>
  @endif

  <form action="{{ route('podcast_auth') }}" method="get">

    <div class="form-group">
      <label for="url">Podcast URL</label>
      <input type="text" class="form-control" name="url" placeholder="http://example.com/" value="{{ session('podcast_url') }}">
      <p class="help-block">Must support Micropub and have a Media Endpoint</p>
    </div>

    <button type="submit" class="btn btn-primary">Connect</button>

  </form>

</div>
@endsection
