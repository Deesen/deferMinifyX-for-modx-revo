## deferMinifyX 0.2

Plugin + Snippet related to SEO-tasks like **defer Javascript-, Css- and Img-files**. Solution for easy/default CSS/JS-setups, as well as more complex ones allowing to chain multiple onLoad-handlers for \<script\> using the snippet.

You can check your website on https://developers.google.com/speed/pagespeed/insights/

### Default use

To set a standard set of CSS- and JS-files, get them minified into min.css/min.js and append nessecary tags to \<head\> and \</body\>, follow these steps:

- Install plugin manually (no transport-package prepared yet)
- Add required default set of CSS- and JS-files comma-separated in plugin-configuration
- Set desired parameters, and especially "defer" to true to enable defer-mechanismn
- Save plugin-configuration, finish!

When reloading your frontpage the CSS- and JS-files should be added to your frontpage as configured. By default the defer-option is disabled and will render normal link- and script-tags. Script-Tags will be added to \</body\> ordered by dependsOn/id relations.

### Above-the-fold

If you want to inject critical parts of CSS directly into the source-code, please refer to the "Snippet Commandos" below.

### Plugin-configuration

Don´t forget to clear Modx-cache to see changes.

| Parameter           | Description | Values (*install default*) |
|----------------------|-------------|--------------------|
|activeIds             | Enable plugin only for these ressource-ids (comma-separated)                              | |
|blankImage            | Filepath to blank-image                                                                   | *img/blank.jpg* |
|cache                 | Caching of minified files (can be disabled for debug)                                     | *enabled*, disabled | 
|cachePath             | Path where to store cache-related files (empty defaults to assets/cache/deferMinifyX)     | |
|context               | Comma-separated list of contexts where to apply plugin to (you can duplicate plugin for different context-setups) | *web* | 
|debug                 | Shows extended debug-infos (in console.log() and HTML-comments, **disable** on live-sites)| enabled, *disabled* | 
|defaultCssFiles       | Comma-separated list, optional: add defer/async: css/style.css\|\|defer\|\|async         | *css/style.css,css/responsive.css* |
|defaultInlineJsChunk  | Default inline-code, will be added to min.js                                              | | 
|defaultJsFiles        | Comma-separated list, optional: add defer/async: js/application.js\|\|defer\|\|async      | *js/jquery.min.js,js/application.js* |
|**defer**             | When disabled normal link- and script-tags with optional defer/async-attributes will be rendered) | enabled, *disabled* | 
|deferImages           | When enabled, it replaces all img src by src="blank.jpg" data-src "real-image.jpg")       | enabled, *disabled* | 
|hashParam             | String to add as cache-param min.js?suffix (enables min.js?*ver=*xxx)                     | |
|inactiveIds           | Disable plugin for these ressource-ids (comma-separated)                                  | |
|minify                | Enable/disable minify globally                                                            | *enabled*, disabled |
|minifyCss             | Minify default CSS-files into min.css	                                                   | *enabled*, disabled |
|minifyCssFile         | FilePath to store, example css/min.css                                                    | *min.css* |
|minifyCssLib          | Which library to use                                                                      | *minifier*          | 
|minifyDefer           | Inject minified defer script (enabled provides no console.log()-debuginfos)               | *enabled*, disabled |
|minifyDefer           | Inject minified defer script (enabled provides no console.log()-debuginfos)               | *enabled*, disabled |
|minifyJs              | Minify default JS-files and inline-code into min.js                                       | *enabled*, disabled | 
|minifyJsFile          | FilePath to store minified JS-files, example css/min.js                                   | *min.js* | 
|minifyJsLib           | Which library to use                                                                      | *minifier*, JShrink |  
|minifyHtml            | Minify HTML-output at runtime                                                             | *disabled*, minifier, regex |
|noScriptCss           | Add CSS-links as fallback                                                                 | *enabled*, disabled |

### More complex chaining example

  - On page loaded, the defer function is called, adding all scripts without dependencies first (= no &dependsOn=\`id\` parameter set). The default set of JS-files (min.js) will be added with ID "min".
  
  - If jQuery is a default file (within min.js), and a component depends on jQuery, you can use ID "min" with &dependsOn=\`min\`
  
        [[!deferMinifyX? &add=`js` &file=`js/slider_xy.min.js` &id=`slider` &dependsOn=`min`]]
        [[!deferMinifyX? &add=`script` &val=`$('.slider').slider();` &dependsOn=`slider` &id=`slider_call`]]

  - you can chain multiple ids and dependsOn per subpage like required. It should be possible to chain all kind of combinations like (otherwise please report an issue ;-) ):
  
        [[!deferMinifyX? &add=`js` &file=`js/slider_tools.min.js` &id=`slider_tools` &dependsOn=`slider_call`]]
        [[!deferMinifyX? &add=`script` &val=`$('.slider').sliderTool('xy');` &dependsOn=`slider_tools`]]
        ...

### Snippet Commandos
#### &add

    [[!deferMinifyX? &add=`css`    &file=`js/your_file.css`]]
    [[!deferMinifyX? &add=`js`     &file=`js/your_file.js` &id=`your__optional_id`]]
    [[!deferMinifyX? &add=`js`     &file=`js/your_file.js` &dependsOn=`your__optional_id`]]
    [[!deferMinifyX? &add=`script` &val=`your code`        &dependsOn=`your__optional_id`]]

Important: Must be called uncached!

#### &get

You can also directly inject base64-encoded images or files, minified css/js-files, or minified strings (use cached snippet-calls!).

    <img    src="[[deferMinifyX? &get=`img64`  &file=`img/your_file.xxx` ]]" alt="" />
    <style>      [[deferMinifyX? &get=`css`    &file=`css/your_file.css` ]]        </style>
    <script src="[[deferMinifyX? &get=`js`     &file=`js/your_file.js` ]]">        </script>
    
    <style>      [[deferMinifyX? &get=`minify` &val=`your rules` ]]                </style>
    <script>     [[deferMinifyX? &get=`minify` &val=`your code` ]]                 </script>
    
                 [[deferMinifyX? &get=`base64` &val=`your string to encode` ]]

So in a "above-the-fold" scenario you can split critical parts of CSS into a separate file, and put this line into `<head>`

    <style>[[deferMinifyX? &get=`css`&file=`css/critical.css` ]]</style>

#### &option

For development most parameters of plugin-configuration can be modified dynamically via

        [[!deferMinifyX? &option=`defer` &val=`0`]]
        [[!deferMinifyX? &option=`deferImages` &val=`1`]]
        ...