# Getting started:

- Add the `tojson` folder to your `craft/plugins` directory.
- Create a template with the following:

		{% header "Content-Type: application/json" %}
		{{ entry | to_json | raw }}

- Done!