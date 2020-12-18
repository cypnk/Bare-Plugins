# Render Plugin

Render is a formatting helper which has templates for other features  
besides those included in Bare core.

There are helpers for some hooks including selects, input, and pagination.

## Installation
Upload this folder to the **PLUGINS** directory defined in *index.php*.

Add *render* to the **PLUGINS_ENABLED** whitelist in *index.php* or  
add to the *plugins_enabled* comma delimited list in *config.json*.  
Other plugins might depend on this plugin so make sure the whitelist has  
the *render* plugin added before those dependents.
