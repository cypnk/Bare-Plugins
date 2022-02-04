# Upload Plugin

Upload is a set of helper functions to let other plugins and Bare handle user submitted files

## Installation
Upload this folder to the **PLUGINS** directory defined in *index.php*.

Add *upload* to the **PLUGINS_ENABLED** whitelist in *index.php* or add to the *plugins_enabled* comma delimited list in *config.json*. If other plugins depend on this one, remember to have this plugin in the whitelist before those are included.
