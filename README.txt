This module add simple token in files URLs based on file modified time. This is
very useful for caching files in CDNs and differentiate if the image changes
like a new resource.

Modules that crop images need this for integrate with CDNs. Because the image
file name is the same but the image could be different.

You can configure:
- Add token for image styles URLs
- Add token for all files URLs
- Define whitelist and blacklist of file extensions

IMPORTANT: The module will generate absolute URLs for avoid encoding conflicts
with GET query parameters.

@todo Add only when files are called by file_create_url

Example file URL without File Version:
http://example.com/sites/default/files/2017-05/example.png

Example file URL with File Version:
http://example.com/sites/default/files/2017-05/example.png?fv=v-malxjm