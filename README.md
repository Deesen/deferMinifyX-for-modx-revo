## deferMinifyX 0.1 alpha

I want to provide an easy solution for most nowadays SEO-tasks related to **defer Javascript-, Css- and Img-files**. When finished this snippet aims to be an all-in-one solution for easy/default setups, as well as more complex ones allowing to chain multiple onLoad-handlers.

#### Snippet parameters / defaults
    [[!deferMinifyX?
        &get=`1`
        &minifyDefer=`1`
        &minifyCss=`1`
        &minifyCssFile=`css/min.css`
        &minifyJs=`1`
        &minifyJsFile=`js/min.js`
        &cacheFile=`js/cache.json`
        &cache=`1`
        &minify=`1`
        &debug=`0`
    ]]

##### Default example <sub><sup>(Note: Replace " with \`)</sup></sub>

  - Add all nessecary CSS-, JS-files comma-separated in a single snippet-call and add it to your &lt;body&gt;, finish!
    `[[deferMinifyX? 
     &addCssSrc="css/bootstrap.css,css/styles.css,css/responsive.css"
     &addJsSrc="css/bootstrap.css,css/styles.css,css/responsive.css"
     &get="1"
    ]]`

##### More complex chaining example

  - on page loaded, call the defer function, which loads all scripts without dependencies (no &dependsOn=\`id\` parameter set) first, in this case "script_src_jquery". For this example we add only jQuery but of course you can add multiple scripts "without dependencies"
    `[[deferMinifyX? &addScriptSrc="js/jquery.min.js" &id="script_src_jquery"]]`
  - if "script_src_jquery" loaded then load "script_src_slider" (which depends on "script_src_jquery")
    `[[deferMinifyX? &addScriptSrc="js/slider.min.js" &dependsOn="script_src_jquery" &id="script_src_slider"]]`
  - if "script_src_slider" loaded then call "script_remove_preloader" + call "script_startslider"
    `[[deferMinifyX? &addScript="$('#preloader').fadeOut();" &dependsOn="script_src_slider" &id="script_remove_preloader"]]`
    `[[deferMinifyX? &addScript="$('.slider').slider('init');" &dependsOn="script_src_slider" &id="script_startslider"]]`
  - if "script_remove_preloader" called then load "script_src_x"
    `[[deferMinifyX? &addScriptSrc="js/script_x.js" &dependsOn="script_remove_preloader"]]`
  - if "script_startslider" called then call "script_sort_bullets"
    `[[deferMinifyX? &addScript="$('.slider').slider('sort');" &dependsOn="script_startslider"]]`
  - if "script_src_x" loaded then ..
  
#### Chaining to "min"

By default the scriptSrc-element for min.js has ID "min", so you can depend on "min" and hook onload-events after "min" is loaded, even if I don´t know yet when it will be needed ;-) :

  - on page loaded, call defer function and load min.js (which has no dependencies) with ID "min"
  - if "min" loaded then load "script_src_dev"
    `[[deferMinifyX? &addScriptSrc="js/dev.js" &dependsOn="min" ]]`
  - if "script_src_dev" loaded then ..
  
#### Debug-Mode

  - &debug=\`1\` provides detailed Debug-infos when logged into manager as HTML-comments + console.log(). Each important step will be logged into console for more insight.
  - &cache=\`0\` forces generating of minified files (for development)
  - &minify=\`0\` globally disables minifying of files (for development)

**Beware of Modx-Cache when in production!** You probably don´t want to leave debug-infos or unminified files in your production-environment by setting &debug=\`1\` .

------------------------------------------------------------------

### Actual version-infos 0.1 alpha
- still in raw development
- tested with Modx Revo 2.4.3
- **not tested in production** 

### Todo:
- finish deferMinifyX-cache
- check interaction of deferMinifyX-cache / Modx-cache
- finish dependsOn-sort mechanismn before minified files
- add "inject css directly to source"
- test adding comma-separated files
- check base-path for file_get_content, file_put_content
- add "defer images" https://varvy.com/pagespeed/defer-images.html
- inline-todos
- check common browsers for compatibility
- port to Modx Evolution