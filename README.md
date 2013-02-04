## iZap Video to videolist migrator

The script was designed to migrate iZap video objects (created in an Elgg 1.6 installation) to objects displayable by the videolist plugin in Elgg 1.8.

It only converts 3rd party hosted videos that can be displayed by videolist. Iframe/object markup is validated and only accepted from trusted sources (see `validateMedia()` in lib.php for details). Unconverted GUIDs are reported with their associated metadata.

USE AT YOUR OWN RISK!

### Instructions

Place this folder in the root of your Elgg installation.

Uncomment the `die()` command on line 3 of `convert.php`.

Log in as an admin.

Each request to `/elgg/videolistfromizap/convert.php` processes 500 objects (configurable via `?limit=N`)

#### BSD licensed

Copyright (c) 2013, Steve Clay
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


### Klatch version, nothing changed but data types, much thanks to Steve Clay.
### If you had not renamed your videos types to remove the izap name you will have to uncomment these lines:

convert.php:$subtype_id = (int) get_subtype_id('object', 'izap_videos');
lib.php:			$glob_pattern = "izap_videos/{$o->videotype}/{$o->time_created}*.jpg";

### And comment out these (in my case, customize to your needs)

convert.php:$subtype_id = (int) get_subtype_id('object', 'videos');
lib.php:			$glob_pattern = "videos/{$o->videotype}/{$o->time_created}*.jpg";

