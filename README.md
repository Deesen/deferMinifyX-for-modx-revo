## deferMinifyX 0.1 alpha

Tested for example with (order not important, duplicate calls/values get sorted out) 
 
    // Defer CSS at onLoad
    [[deferMinifyX?&addCssSrc=`css/dev.css`]]
    [[deferMinifyX?&addCssSrc=`css/dev2.css`]]
    
    // No "dependsOn" = scripts get loaded first, jquery get id "jquery"
    [[deferMinifyX?&addScriptSrc=`js/jquery.min.js`&id=`jquery`]]
    [[deferMinifyX?&addScriptSrc=`js/webfonts.js`]]
    
    // Script "dependsOn" jquery, so load after jquery
    [[deferMinifyX?&addScriptSrc=`js/application.js`&dependsOn=`jquery`]]
    [[deferMinifyX?&addScriptSrc=`js/unslider-min.js`&id=`unslider`&dependsOn=`jquery`]]
     
    // Script "dependsOn" unslider, so load after unslider is loaded
    [[deferMinifyX?&addScript=`
        $('#slider').unslider({
            animation: 'fade',
            autoplay: true,
            delay:5000,
            nav:false,
            arrows: false
        });
        $('#preloader').delay(100).fadeOut('slow', function(){
            $(this).remove();
        });
        $('#slider ul').fadeIn('slow');
    `&dependsOn=`unslider`]]
    
    // "dependsOn" = script gets called after script['min'] is loaded successfully
    [[deferMinifyX?&addScriptSrc=`js/dev.js`&dependsOn=`min`]]
    [[deferMinifyX?&addScript=`alert('addScript dependsOn min');`&dependsOn=`min`&unique=`1`]]
    [[deferMinifyX?&addScript=`alert('addScript dependsOn min2');`&dependsOn=`min`&unique=`1`]]
    
    // Final call to inject deferScript-magic
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