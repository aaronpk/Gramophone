# Gramophone

Gramophone is a podcast publishing Micropub client.

## Installation

Gramophone is based on the Laravel framework. You should follow the normal Laravel installation steps, with the following additions:

* Install ffmpeg and configure the full path to the ffmpeg binary in `.env`
* You will need to use the file storage method, and set the full path to the folder in `.env`

Make sure the necessary folders are writable by the web server:

* `bootstrap/cache`
* `storage`

You'll need to run the following Laravel setup commands:

* `php artisan key:generate` - creates the secret key used for session encryption
* `php artisan migrate` - sets up the database schema
* `php artisan storage:link` - makes a symlink from the storage folder to the public web folder

## License

Gramophone is available under the MIT license.
