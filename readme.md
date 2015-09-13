# toJSON

A Craft plugin for exporting your entries to JSON. It provides the following features:

- supports most field types including Entries, Matrix blocks and Assets
- it applies all image transformation automatically

The following is an example output of the plugin, noting that the content has been removed:

    {
      "id": "1",
      "slug": "...",
      "uri": "...",
      "postDate": "2015-08-23T04:51:00+00:00",
      "dateCreated": "2015-08-23T04:51:53+00:00",
      "dateUpdated": "2015-08-29T02:01:13+00:00",
      "_model": "post",
      "_section": "posts",
      "title": "...",
      "subtitle": "...",
      "categories": [],
      "poster": [
        {
          "id": "12",
          "slug": "screen-shot-2015-08-23-at-2-41-02-pm",
          "uri": null,
          "dateCreated": "2015-08-23T04:54:57+00:00",
          "dateUpdated": "2015-08-24T11:26:36+00:00",
          "title": "...",
          "url": "...",
          "width": "542",
          "height": "468",
          "variations": {
            "squareMedium": {
              "url": "...",
              "width": "400",
              "height": "400"
            },
            "squareSmall": {
              "url": "...",
              "width": "200",
              "height": "200"
            }
          },
          "credit": "",
          "customWidth": 0,
          "customHeight": 0,
          "customMode": null,
          "customPosition": null,
          "customFormat": null,
          "customQuality": 0
        }
      ],
      "body": "...",
      "bodyMixed": [
        {
          "id": "14",
          "slug": "",
          "uri": null,
          "dateCreated": "2015-08-23T05:20:04+00:00",
          "dateUpdated": "2015-08-29T02:01:13+00:00",
          "_model": "markdown",
          "markdown": "..."
        },
        {
          "id": "16",
          "slug": "",
          "uri": null,
          "dateCreated": "2015-08-23T05:20:04+00:00",
          "dateUpdated": "2015-08-29T02:01:13+00:00",
          "_model": "image",
          "image": [
            {
              "id": "12",
              "slug": "...",
              "uri": null,
              "dateCreated": "2015-08-23T04:54:57+00:00",
              "dateUpdated": "2015-08-24T11:26:36+00:00",
              "title": "...",
              "url": "...",
              "width": "542",
              "height": "468",
              "variations": {
                "squareMedium": {
                  "url": "...",
                  "width": "400",
                  "height": "400"
                },
                "squareSmall": {
                  "url": "...",
                  "width": "200",
                  "height": "200"
                }
              },
              "credit": "",
              "customWidth": 0,
              "customHeight": 0,
              "customMode": null,
              "customPosition": null,
              "customFormat": null,
              "customQuality": 0
            }
          ],
          "alignment": "right",
          "widthClass": "col-sm-3"
        }
      ]
    }



## Getting started

- Add the `tojson` folder to your `craft/plugins` directory.

  *Advanced tip:* You can add the plugin as a Git submodule, which makes it easier to keep it up-to-date.

  Add module:

  		git submodule add git@github.com:torbensko/tojson.git craft/plugins/tojson

  Update module:

      cd craft/plugins/tojson
      git pull

- Within Craft, enable the plugin under the plugin menu.

- Create an entry template with the following:

		{% header "Content-Type: application/json" %}
		{{ entry | to_json | raw }}

- Done!


## Advanced

The plugin should work out of the box, but there are a few extra things it can do to help out.


### Image transformations

The plugin applies all transformations to each image. The resulting URLs are placed in a `variations` field.

You can also manually resize each image by adding the following fields to your asset source:

- *customWidth*
- *customHeight*
- *customQuality*
- *customMode*
- *customPosition*
- *customFormat*

The allowable values for each can be found on the [Craft site](http://buildwithcraft.com/docs/image-transforms). The resulting image will be added to the `variations` field under the name `_custom`.

Your resulting JSON will look something like:

    {
      "id": "28",
      "slug": "sw-blake-bronstad-1400x965",
      "uri": null,
      "title": "Sleepy kitty",
      "url": "http://s3-ap-southeast-2.amazonaws.com/torbenskocom-assets/SW_Blake-Bronstad-1400x965.jpg",
      "width": "1400",
      "height": "965",
      "variations": {
        "squareMedium": {
          "url": "http://s3-ap-southeast-2.amazonaws.com/torbenskocom-assets/_squareMedium/SW_Blake-Bronstad-1400x965.jpg",
          "width": "400",
          "height": "400"
        },
        "squareSmall": {
          "url": "http://s3-ap-southeast-2.amazonaws.com/torbenskocom-assets/_squareSmall/SW_Blake-Bronstad-1400x965.jpg",
          "width": "200",
          "height": "200"
        },
        "_custom": {
          "mode": "crop",
          "width": "600",
          "height": "200",
          "quality": "50",
          "position": "center-left",
          "url": "http://s3-ap-southeast-2.amazonaws.com/torbenskocom-assets/_600x200_crop_center-left_50/SW_Blake-Bronstad-1400x965.jpg"
        }
      },
      "credit": "Sourced from Magdeleine.co",
      "customWidth": 600,
      "customHeight": 200,
      "customMode": "crop",
      "customPosition": "center-left",
      "customFormat": null,
      "customQuality": 50
    }


*Advanced:* if you are using the [Art Vandelay - Import/Export plugin](https://github.com/xodigital/ArtVandelay), you can import these fields using the following JSON:

    {
      "assets": [],
      "categories": [],
      "fields": {
        "Images": {
          "customFormat": {
            "name": "Format",
            "context": "global",
            "instructions": "",
            "translatable": 0,
            "type": "Dropdown",
            "settings": {
              "options": [
                {
                  "label": "Auto",
                  "value": "",
                  "default": ""
                },
                {
                  "label": "JPEG",
                  "value": "jpg",
                  "default": ""
                },
                {
                  "label": "PNG",
                  "value": "png",
                  "default": ""
                },
                {
                  "label": "GIF",
                  "value": "gif",
                  "default": ""
                }
              ]
            }
          },
          "customHeight": {
            "name": "Height",
            "context": "global",
            "instructions": "",
            "translatable": 0,
            "type": "Number",
            "settings": {
              "min": 0,
              "max": "",
              "decimals": 0
            }
          },
          "customMode": {
            "name": "Mode",
            "context": "global",
            "instructions": "",
            "translatable": 0,
            "type": "RadioButtons",
            "settings": {
              "options": [
                {
                  "label": "None",
                  "value": "",
                  "default": ""
                },
                {
                  "label": "Crop",
                  "value": "crop",
                  "default": ""
                },
                {
                  "label": "Fit",
                  "value": "fit",
                  "default": ""
                },
                {
                  "label": "Stretch",
                  "value": "stretch",
                  "default": ""
                }
              ]
            }
          },
          "customPosition": {
            "name": "Position",
            "context": "global",
            "instructions": "Only applicable when using crop mode",
            "translatable": 0,
            "type": "RadioButtons",
            "settings": {
              "options": [
                {
                  "label": "Top-Left",
                  "value": "top-left",
                  "default": ""
                },
                {
                  "label": "Top-Center",
                  "value": "top-center",
                  "default": ""
                },
                {
                  "label": "Top-Right",
                  "value": "top-right",
                  "default": ""
                },
                {
                  "label": "Center-Left",
                  "value": "center-left",
                  "default": ""
                },
                {
                  "label": "Center-Center",
                  "value": "center-center",
                  "default": ""
                },
                {
                  "label": "Center-Right",
                  "value": "center-right",
                  "default": ""
                },
                {
                  "label": "Bottom-Left",
                  "value": "bottom-left",
                  "default": ""
                },
                {
                  "label": "Bottom-Center",
                  "value": "bottom-center",
                  "default": ""
                },
                {
                  "label": "Bottom-Right",
                  "value": "bottom-right",
                  "default": ""
                }
              ]
            }
          },
          "customQuality": {
            "name": "Quality",
            "context": "global",
            "instructions": "",
            "translatable": 0,
            "type": "Number",
            "settings": {
              "min": 0,
              "max": 100,
              "decimals": 0
            }
          },
          "customWidth": {
            "name": "Width",
            "context": "global",
            "instructions": "",
            "translatable": 0,
            "type": "Number",
            "settings": {
              "min": 0,
              "max": "",
              "decimals": 0
            }
          }
        }
      },
      "globals": [],
      "sections": [],
      "contenttabs": [],
      "tags": []
    }


### Meta details

The plugin provides the following additional details: 

* *_model*: The template name for the entry or matrix block.
* *_section*: The name of the section



## To do

- Support for User entries
- Detection of cyclic models
- Max entry depth as a parameter
- Add toggleable schema details
- Make it possible to toggle the format of the dates
