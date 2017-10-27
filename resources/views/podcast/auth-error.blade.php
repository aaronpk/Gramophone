@extends('layouts.main')

@section('content')

<div class="container">
  <br>

  <div class="alert alert-danger">
    <strong>{{ $error }}</strong> 
    <p>{{ $description }}</p>
  </div>

</div>

@endsection
