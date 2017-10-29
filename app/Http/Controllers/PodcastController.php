<?php
namespace App\Http\Controllers;

use Request, DB, Gate, Storage;
use App\User, App\Podcast, App\Episode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PodcastController extends Controller
{

  public function __construct() {
    $this->middleware('auth');
  }

  public function create() {
    return view('podcast/new');
  }

  public function podcast(Podcast $podcast) {
    if(Gate::allows('edit-podcast', $podcast)) {
      return view('podcast/info', [
        'podcast' => $podcast
      ]);
    } else {
      abort(401);
    }
  }

  public function edit(Podcast $podcast) {
    if(Gate::allows('edit-podcast', $podcast)) {
      return view('podcast/edit', [
        'podcast' => $podcast
      ]);
    } else {
      abort(401);
    }
  }

  public function update(Podcast $podcast) {
    if(Gate::allows('edit-podcast', $podcast)) {

      $podcast->name = Request::input('name');
      $podcast->author = Request::input('author');
      $podcast->genre = Request::input('genre');
      $podcast->cover_image = Request::input('cover_image');
      $podcast->save();

      return redirect(route('podcast', ['podcast'=>$podcast]));

    } else {
      abort(401);
    }
  }

  public function upload() {
    $podcast = Podcast::where('id', Request::input('podcast'))->first();
    if($podcast && Gate::allows('edit-podcast', $podcast)) {

      $file = Request::file('file');

      if($file->isValid()) {

        if(in_array($file->getMimeType(), ['audio/mp4','audio/mpga'])) {

          switch($file->getMimeType()) {
            case 'audio/mp4':
              $ext = 'm4a'; break;
            case 'audio/mpga':
              $ext = 'mp3'; break;
          }

          $bitrate = round(Request::input('bitrate')).'k';
          if(Request::input('channels') == 'mono') 
            $mono = '-ac 1';
          else
            $mono = '';

          $filename = $file->store('audio');
          $new_filename = $podcast->id.'_'.date('Ymd_His').'.mp3';

          $cmd = env('FFMPEG_BIN').' -i "'.env('STORAGE_FOLDER').'/'.$filename.'" -b:a '.$bitrate.' '.$mono.' '.env('STORAGE_FOLDER').'/public/audio/'.$new_filename.' 2>&1';
          $output = shell_exec($cmd);

          if(Storage::exists('public/audio/'.$new_filename)) {

            $size = round(Storage::size('public/audio/'.$new_filename)/1024/1024, 2).'mb';
            
            $id3 = new \getID3;
            $info = $id3->analyze(env('STORAGE_FOLDER').'/public/audio/'.$new_filename);

            return response()->json([
              'id' => $new_filename,
              'filesize' => $size,
              'duration' => gmdate('i:s', $info['playtime_seconds']),
              'url' => '/storage/audio/'.$new_filename,
            ]);
          } else {
            return response()->json([
              'error' => 'encoding_error'
            ]);
          }

        } else {
          return response()->json([
            'error' => 'invalid_format'
          ]);
        }

      } else {
        return response()->json([
          'error' => 'invalid_file'
        ]);
      }

    } else {
      abort(401);
    }
  }

  public function save_id3() {
    $podcast = Podcast::where('id', Request::input('podcast'))->first();
    if($podcast && Gate::allows('edit-podcast', $podcast)) {

      // Download cover art
      $tmpfile = tempnam(sys_get_temp_dir(), 'gramophone-cover');
      $client = new Client();
      $client->request('GET', $podcast->cover_image, [
        'sink' => $tmpfile
      ]);

      $full_filename = env('STORAGE_FOLDER').'/public/audio/'.Request::input('id');
      if(!file_exists($full_filename)) {
        abort(400);
      }

      // Write ID3 tags
      $getID3 = new \getID3;

      $tagwriter = new \getid3_writetags;
      $taggingFormat = 'UTF-8';
      $tagwriter->filename       = $full_filename;
      $tagwriter->tagformats     = ['id3v2.3'];
      $tagwriter->overwrite_tags = true;
      $tagwriter->tag_encoding   = $taggingFormat;
      $tagwriter->remove_other_tags = true;

      $data['Title'] = [Request::input('name')];
      $data['Track'] = [Request::input('number')];
      $data['Genre'] = [$podcast->genre];
      $data['Artist'] = [$podcast->author];
      $data['Album'] = [$podcast->name];
      $data['Year'] = [Request::input('date')];
      $data['Comment'] = ['description' => Request::input('description')];

      $picture = [
        'picturetypeid' => 3,
        'description' => 'cover.jpg',
        'mime' => 'image/jpeg',
        'data' => file_get_contents($tmpfile)
      ];
      $data['attached_picture'] = [$picture];

      $tagwriter->tag_data = $data;
      $tagwriter->WriteTags();

      // Return "ok"
      return response()->json([
        'result' => 'ok'
      ]);

    } else {
      abort(401);
    }
  }

  public function upload_media() {
    $podcast = Podcast::where('id', Request::input('podcast'))->first();
    if($podcast && Gate::allows('edit-podcast', $podcast)) {

      $full_filename = env('STORAGE_FOLDER').'/public/audio/'.Request::input('id');
      if(!file_exists($full_filename)) {
        abort(400);
      }

      $num = Request::input('number');

      // Upload to media endpoint
      try {
        $client = new Client();
        $response = $client->request('POST', $podcast->media_endpoint, [
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'Gramophone/1.0',
            'Authorization' => 'Bearer '.$podcast->access_token
          ],
          'multipart' => [
            [
              'name' => 'file',
              'contents' => fopen($full_filename, 'r'),
              'filename' => 'Episode_'.$num.'.mp3',
            ]
          ]
        ]);

        if($response->hasHeader('Location')) {
          $media_url = $response->getHeader('Location')[0];

          // Return resulting URL
          return response()->json([
            'result' => 'ok',
            'url' => $media_url,
            'code' => $response->getStatusCode(),
            'body' => (string)$response->getBody(),
          ]);
        } else {
          return response()->json([
            'result' => 'error'
          ]);
        }
      } catch(RequestException $e) {
        return response()->json([
          'result' => 'error',
          'error' => $e->getMessage()
        ]);
      }

    } else {
      abort(401);
    }
  }

  public function create_episode() {
    $podcast = Podcast::where('id', Request::input('podcast'))->first();
    if($podcast && Gate::allows('edit-podcast', $podcast)) {

      // Upload to Micropub endpoint
      $params = [
        'type' => ['h-entry'],
        'properties' => [
          'name' => [Request::input('name')],
          'episode' => [Request::input('number')],
          'published' => [Request::input('date')],
          'duration' => [Request::input('duration')],
          'audio' => [Request::input('audio')],
          'summary' => [Request::input('summary')],
          'content' => [Request::input('description')]
        ]
      ];

      try {
        $client = new Client();
        $response = $client->request('POST', $podcast->micropub_endpoint, [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Gramophone/1.0',
            'Authorization' => 'Bearer '.$podcast->access_token
          ],
          'body' => json_encode($params)
        ]);
        if($response->getStatusCode() == 201) {
          if($response->hasHeader('Location')) {
            $episode_url = $response->getHeader('Location')[0];

            // Save the new episode number
            $podcast->last_episode_number = (int)Request::input('number');
            $podcast->save();

            // Log this episode as complete
            $episode = new Episode;
            $episode->podcast_id = $podcast->id;
            $episode->name = Request::input('name');
            $episode->episode = Request::input('number');
            $episode->date = Request::input('date');
            $episode->duration = Request::input('duration');
            $episode->summary = Request::input('summary');
            $episode->content = Request::input('description');
            $episode->episode_url = $episode_url;
            $episode->audio_url = Request::input('audio');
            $episode->save();

            return response()->json([
              'result' => 'ok',
              'location' => $episode_url
            ]);
          } else {
            return response()->json([
              'result' => 'error',
              'error' => 'The Micropub endpoint did not return a Location header'
            ]);
          }
        } else {
          return response()->json([
            'result' => 'error',
            'error' => 'The Micropub endpoint did not return HTTP 201'
          ]);
        }
      } catch(RequestException $e) {
        return response()->json([
          'result' => 'error',
          'error' => $e->getMessage()
        ]);
      }

    } else {
      abort(401);
    }
  }


}

