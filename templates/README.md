# Bare Templates

This is a simple handler which enables overriding all templates in [index.php](https://github.com/cypnk/Bare/blob/master/index.php).

Simply create a file in the **files/** subfolder with the name of the template.  
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
