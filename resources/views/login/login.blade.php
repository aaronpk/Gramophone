@extends('layouts.main')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>
                <div class="panel-body">

                    @if(session('auth_error'))
                      <div class="alert alert-danger">
                        <strong>{{ session('auth_error') }}</strong> 
                        <p>{{ session('auth_error_description') }}</p>
                      </div>
                    @endif

                    <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
                            <label for="url" class="col-md-4 control-label">Your Website</label>

                            <div class="col-md-6">
                                <input id="url" type="url" class="form-control" name="url" value="{{ session('auth_url') }}" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
