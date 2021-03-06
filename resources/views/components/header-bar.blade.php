  <nav class="navbar navbar-default navbar-static-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">{{ env('APP_NAME') }}</a>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
          @if(Auth::user())
            <li><a href="/dashboard">Dashboard</a></li>
          @endif
        </ul>
        <ul class="nav navbar-nav navbar-right">
          @if(Auth::user())
            <li><a href="/logout">Log Out</a></li>
          @endif
        </ul>
      </div>
    </div>
  </nav>
