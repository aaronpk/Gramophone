@extends('layouts.main')

@section('content')

<div class="container-fluid">
  <br>

  <div class="alert alert-danger">
    <strong>{{ $error }}</strong> {{ $description }}
  </div>

</div>

@endsection
