<?php
namespace App\Http\Controllers;

use Request, DB;
use Auth;
use IndieAuth;
use App\User, App\Podcast;

class PodcastAuthController extends Controller
{

  public function start() {
    if(!Request::input('url')) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'invalid url')
        ->with('auth_error_description', 'The URL you entered was not valid')
        ->with('podcast_url', Request::input('url'));
    }

    // Discover the endpoints
    $url = IndieAuth\Client::normalizeMeURL(Request::input('url'));
    $authorizationEndpoint = IndieAuth\Client::discoverAuthorizationEndpoint($url);

    if(!$authorizationEndpoint) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'missing authorization endpoint')
        ->with('auth_error_description', 'Could not find your authorization endpoint')
        ->with('podcast_url', Request::input('url'));
    }

    $tokenEndpoint = IndieAuth\Client::discoverTokenEndpoint($url);

    if(!$tokenEndpoint) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'missing token endpoint')
        ->with('auth_error_description', 'Could not find your token endpoint')
        ->with('podcast_url', Request::input('url'));
    }

    $micropubEndpoint = IndieAuth\Client::discoverMicropubEndpoint($url);
    $mediaEndpoint = IndieAuth\Client::discoverMediaEndpoint($url);

    if(!$micropubEndpoint) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'missing micropub endpoint')
        ->with('auth_error_description', 'Could not find your Micropub endpoint')
        ->with('podcast_url', Request::input('url'));
    }

    if(!$mediaEndpoint) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'missing authorization endpoint')
        ->with('auth_error_description', 'Could not find your Media Endpoint. A Media Endpoint is required to upload podcast audio files.')
        ->with('podcast_url', Request::input('url'));
    }

    $state = str_random(32);
    session([
      'state' => $state,
      'authorization_endpoint' => $authorizationEndpoint,
      'token_endpoint' => $tokenEndpoint,
      'micropub_endpoint' => $micropubEndpoint,
      'media_endpoint' => $mediaEndpoint,
      'indieauth_url' => $url,
    ]);

    $redirect_uri = route('podcast_auth_callback');
    $client_id = route('index');

    $authorizationURL = IndieAuth\Client::buildAuthorizationURL($authorizationEndpoint, $url, $redirect_uri, $client_id, $state, 'create media');

    return redirect($authorizationURL);
  }

  public function callback() {
    if(!session('state')) {
      self::_clearSession();
      return redirect(route('new_podcast'));
    }

    if(!Request::input('state')) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'missing state')
        ->with('auth_error_description', 'No state was provided in the callback. The IndieAuth server may be configured incorrectly.')
        ->with('podcast_url', session('indieauth_url'));
    }

    if(Request::input('state') != session('state')) {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'invalid state')
        ->with('auth_error_description', 'The state returned in the callback did not match the expected value.')
        ->with('podcast_url', session('indieauth_url'));
    }

    $redirect_uri = route('podcast_auth_callback');
    $client_id = route('index');

    $token = IndieAuth\Client::getAccessToken(session('token_endpoint'), Request::input('code'), session('indieauth_url'), $redirect_uri, $client_id);

    if(isset($token['me'])) {

      // Check if we've already connected the podcast and update if so
      $podcast = Podcast::where('user_id', Auth::user()->id)->where('url', $token['me'])->first();
      if(!$podcast) {
        $podcast = new Podcast;
        $podcast->user_id = Auth::user()->id;
        $podcast->url = $token['me'];
      }
      $podcast->micropub_endpoint = session('micropub_endpoint');
      $podcast->media_endpoint = session('media_endpoint');
      $podcast->access_token = $token['access_token'];
      $podcast->name = $token['me'];
      $podcast->save();

      self::_clearSession();
      return redirect(route('dashboard'));

    } else {
      self::_clearSession();
      return redirect(route('new_podcast'))->with('auth_error', 'error authenticating')
        ->with('auth_error_description', 'The token endpoint failed to return an access token.')
        ->with('podcast_url', session('indieauth_url'));
    }
  }

  private static function _clearSession() {
    session([
      'state' => false,
      'authorization_endpoint' => false,
      'token_endpoint' => false,
      'micropub_endpoint' => false,
      'media_endpoint' => false,
      'indieauth_url' => false
    ]);
  }

}

