@extends('layouts/main')

@section('content')
<div class="container">
  <h2>{{ $podcast->name }}</h2>

  <form action="{{ route('update_podcast', ['podcast'=>$podcast]) }}" method="post" role="form">
    {{ csrf_field() }}

    <p>Set up your podcast info here. This info will be embedded in the ID3 tags of each episode.</p>

    <div class="form-group">
      <label for="name">Podcast Name</label>
      <input type="text" class="form-control" name="name" placeholder="" value="{{ $podcast->name }}">
    </div>

    <div class="form-group">
      <label for="author">Author</label>
      <input type="text" class="form-control" name="author" placeholder="" value="{{ $podcast->author }}">
    </div>

    <div class="form-group">
      <label for="genre">Genre</label>
      <input type="text" class="form-control" name="genre" placeholder="" value="{{ $podcast->genre }}">
    </div>

    <div class="form-group">
      <label for="cover_image">Cover Image</label>
      <input type="url" class="form-control" name="cover_image" placeholder="" value="{{ $podcast->cover_image }}">
      <p class="help-block">Enter a URL to the cover image for your podcast</p>
    </div>

    <input type="submit" value="Save" class="btn btn-primary">

  </form>

</div>
@endsection
