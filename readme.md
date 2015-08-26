# toJSON

A Craft plugin for exporting your entries to JSON. It provides the following features:

- supports all field types including Entries, Matrix blocks and Assets
- it applies all image transformation automatically



## Getting started

- Add the `tojson` folder to your `craft/plugins` directory.

  If you would prefer not to mannually copy the repo files, 
  you can add it as a Git submodule. This allows you
  update it in the future via Git. 

  To do so, run the command:

  		git submodule add git@github.com:torbensko/tojson.git craft/plugins/tojson

- Within Craft, enable the plugin under the plugin menu.

- Create an entry template with the following:

		{% header "Content-Type: application/json" %}
		{{ entry | to_json | raw }}

- Done!


## Extras

### Image transformation

To make it possible to apply an image transformation to your images, the system
automatically applies all transformations to each image. The resulting URLs are
placed in a variations field, like so:

    ...
    "url": "http://SERVER/cpresources/transforms/8?x=E2VKeJMte",
    "variations": {
      "TRANSFORM_NAME": {
        "url": "http://S3.amazonaws.com/BUCKET/TRANSFORM_NAME/IMAGE.jpg",
        "width": "300",
        "height": "300"
      },
      "TRANSFORM_NAME": {
        "url": "http://S3.amazonaws.com/BUCKET/TRANSFORM_NAME/IMAGE.jpg",
        "width": "400",
        "height": "400"
      }
    }

You can also manually resize each image. To do so add the following fields to
your asset source:

- imageWidth
- imageHeight
- imageQuality
- imageMode:
  fit, crop, stretch
- imageCropPosition: 
  top-left, top-center, top-right
  center-left, center-center
  center-right, bottom-left, bottom-center, bottom-right