@extends('layouts/main')

@section('content')
<div class="container">
  <h2>{{ $podcast->name }} <a href="{{ route('edit_podcast', ['podcast'=>$podcast]) }}" class="edit-link">edit</a></h2>

  <br>
  
  <h4>Create New Episode</h4>

  <!-- Step 1 -->
  <div id="choose-audio-file">
    <input type="hidden" id="podcast_id" value="{{ $podcast->id }}">

    <div class="form-group">
      <label>Audio File</label>
      <input type="file" id="audio-file">
    </div>

    <div class="form-group">
      <select class="form-control" id="bitrate">
        <option value="96">96k</option>
        <option value="128" selected="selected">128k</option>
        <option value="160">160k</option>
        <option value="192">192k</option>
        <option value="256">256k</option>
      </select>
    </div>

    <div class="form-group">
      <select class="form-control" id="channels">
        <option value="mono">Mono</option>
        <option value="stereo">Stereo</option>
      </select>
    </div>

    <p class="help-block">Choose an audio file and it will be converted to mp3. You will be able to add episode information in the next step.</p>

    <button class="btn btn-primary" id="upload-audio-btn">
      <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate hidden"></span> 
      Upload
    </button>

  </div>

  <!-- Step 2 -->
  <div id="audio-file-uploaded" class="hidden">
    <p><b>File Prepared:</b> <span id="audio-file-size"></span> <span id="audio-file-duration"></span></p>

    <input type="hidden" id="audio-file-id">

    <div class="form-group">
      <label for="episode_name">Episode Name</label>
      <input type="text" class="form-control" id="episode_name" placeholder="" value="Episode {{ $podcast->number+1 }}">
    </div>

    <div class="form-group">
      <label for="episode_number">Episode Number</label>
      <input type="number" class="form-control" id="episode_number" value="{{ $podcast->number+1 }}">
    </div>

    <div class="form-group">
      <label for="episode_date">Date</label>
      <input type="date" class="form-control" id="episode_date" value="{{ date('Y-m-d') }}">
    </div>

    <div class="form-group">
      <label for="episode_summary">Summary</label>
      <input type="text" class="form-control" id="episode_summary" placeholder="One-line episode summary">
    </div>

    <div class="form-group">
      <label for="episode_description">Description</label>
      <textarea class="form-control" id="episode_description" placeholder="Episode description" rows="4"></textarea>
    </div>

    <button class="btn btn-primary" id="publish-btn">
      <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate hidden"></span> 
      Publish
    </button>

  </div>
  <div id="upload-progress" class="hidden" style="margin-top: 1em;">
    <p>Progress</p>
    <ul></ul>
  </div>

</div>
@endsection
