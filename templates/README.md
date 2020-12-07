# Bare Templates

This is a simple handler which enables overriding all templates in Bare

To install, upload the templates folder to the PLUGINS directory and add 'templates' to  
PLUGINS_ENABLED in the main [index.php](https://github.com/cypnk/Bare/blob/master/index.php) or add to 'plugins_enabled' in *config.json*.  
This isn't a core plugin so templates can be set the last plugin in that list.

To use, create a file in the **files/** subfolder with the name of a template.  
E.G. If overriding *tpl_full_page*, create **files/tpl_full_page.tpl** with your own.  
Then in cache/config.json add the following:

```
{
--- snip: your other settings---
templates: [ "tpl_full_page" ]

--- continue other settings---
}
```

This lets the plugin know to override the full page template.  
You can initialize mutiple templates this way

```
templates: [ 
	"tpl_full_page",
	"tpl_page_footer",
	"tpl_page_heading"
]
```

Then create your templates in **files/**:  
*tpl_full_page.tpl*, *tpl_page_footer.tpl*, and *tpl_page_heading.tpl*
