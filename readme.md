# Getting started:

- Add the `tojson` folder to your `craft/plugins` directory.

  If you would prefer not to mannually copy the repo files, 
  you can instead add it as a Git submodule. This allows you easily 
  update it in the future. To do so, run the command:

  		git submodule add git@github.com:torbensko/tojson.git craft/plugins/tojson

- Within Craft, enable the plugin under the plugin menu.

- Create an entry template with the following:

		{% header "Content-Type: application/json" %}
		{{ entry | to_json | raw }}

- Done!