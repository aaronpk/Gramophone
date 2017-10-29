
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

function csrf_token() {
  return $('meta[name="csrf-token"]').attr('content');
}

/* Podcast episode uploading */
jQuery(function($){

  $("#upload-audio-btn").click(function(){

    if(document.getElementById("audio-file").files[0] == null) {
      return false;
    }

    var formData = new FormData();
    formData.append("podcast", $("#podcast_id").val());
    formData.append("bitrate", $("#bitrate").val());
    formData.append("channels", $("#channels").val());
    formData.append("file", document.getElementById("audio-file").files[0]);
    formData.append("_token", csrf_token());

    var request = new XMLHttpRequest();
    request.open("POST", "/podcast/episode/upload");
    request.onreadystatechange = function() {
      if(request.readyState == XMLHttpRequest.DONE) {
        try {
          var response = JSON.parse(request.responseText);
          if(response.id) {
            $("#choose-audio-file").addClass("hidden");
            $("#audio-file-uploaded").removeClass("hidden");
            $("#audio-file-size").text(response.filesize);
            $("#audio-file-duration").text(response.duration);
            $("#audio-file-id").val(response.id);
            $("#audio-file-player").attr("src", response.url);
          } else {
          $("#upload-audio-btn").removeClass("disabled");
            $("#upload-audio-btn .glyphicon").addClass("hidden");
          }
        } catch(e) {
          $("#upload-audio-btn").removeClass("disabled");
          $("#upload-audio-btn .glyphicon").addClass("hidden");
        }
      }
    }
    $("#upload-audio-btn").addClass("disabled");
    $("#upload-audio-btn .glyphicon").removeClass("hidden");
    request.send(formData);
  });

  function enable_publish_btn() {
    $("#publish-btn").removeClass("disabled");
    $("#publish-btn .glyphicon").addClass("hidden");
  }
  function disable_publish_btn() {
    $("#publish-btn").addClass("disabled");
    $("#publish-btn .glyphicon").removeClass("hidden");
  }

  $("#publish-btn").click(function(){
    disable_publish_btn();
    $("#upload-progress").removeClass("hidden");

    /* First write ID3 tags to local copy of file */
    $("#upload-progress ul").append("<li>Writing ID3 tags...</li>");
    $.post("/podcast/episode/save_id3", {
      podcast: $("#podcast_id").val(),
      id: $("#audio-file-id").val(),
      name: $("#episode_name").val(),
      number: $("#episode_number").val(),
      date: $("#episode_date").val(),
      summary: $("#episode_summary").val(),
      description: $("#episode_description").val(),
      _token: csrf_token()
    }, function(response) {

      if(response.result != "ok") {
        enable_publish_btn();
        $("#upload-progress ul").append("<li>There was an error saving the ID3 tags!</li>");
        return;
      }

      /* Upload mp3 file to media endpoint */
      $("#upload-progress ul").append("<li>Uploading file to Media Endpoint...</li>");
      $.post("/podcast/episode/upload_media", {
        podcast: $("#podcast_id").val(),
        id: $("#audio-file-id").val(),
        number: $("#episode_number").val(),
        _token: csrf_token()
      }, function(media_response) {

        if(media_response.result != "ok") {
          enable_publish_btn();
          $("#upload-progress ul").append("<li>There was an error uploading the mp3 file to the Media Endpoint!</li>");
          return;
        }

        /* Send the POST request to the Micropub endpoint */
        $("#upload-progress ul").append("<li>Creating episode...</li>");
        $.post("/podcast/episode/create_episode", {
          podcast: $("#podcast_id").val(),
          audio: media_response.url,
          name: $("#episode_name").val(),
          number: $("#episode_number").val(),
          date: $("#episode_date").val(),
          duration: $("#audio-file-duration").text(),
          summary: $("#episode_summary").val(),
          description: $("#episode_description").val(),
          _token: csrf_token()
        }, function(response) {
          /* all done */

          if(response.result != "ok") {
            enable_publish_btn();
            $("#upload-progress ul").append("<li>There was an error creating the post at the Micropub endpoint!</li>");
            return;
          }

          $("#upload-progress ul").append("<li>Done!</li>");
          $("#upload-progress ul").append('<li>Your episode: <a href="'+response.location+'">'+response.location+'</a></li>');
          enable_publish_btn();
          $("#audio-file-uploaded").addClass("hidden");

        });

      });

    });


  });


});
