# Contao IE9 CSS 4096 selector fix
This extension for Contao Open Source CMS solves a tricky problem on large sites.

IE9 and below (god help us) only recognize the first 4095 selectors in a CSS file. Anything 4096+ is ignored. I know you're saying to yourself:

`Internet Explorer? Screwing up the internet? Never!`

But it's true. This extension will scan your nicely compiled/minified CSS files from Contao and add anything beyond the 4095 selector limit to some additional files and add a handy IE conditional selector for these files to the head tag of your frontend template. Nothing needed from your end.

This extension uses [dlundgren/php-css-splitter](https://github.com/dlundgren/php-css-splitter) to split up the files.
