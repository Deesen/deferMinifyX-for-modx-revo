<?php
/**
 * deferMinify
 * Sets up an array of only unique strings
 *
 * @category                Snippet
 * @version                 0.1 alpha
 * @date                    17.03.2016
 * @author                  dh@fuseit.de
 
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

 */

$deferMinifyX_base_path = $modx->getOption('deferMinifyX.core_path',$scriptProperties,$modx->getOption('core_path').'components/deferMinifyX/');
include_once($deferMinifyX_base_path.'class/class.deferMinifyX.php');

// $deferMinifyX_base_url = $modx->getOption('site_url', $scriptProperties, $modx->getOption('site_url').'components/deferMinifyX/');

// Commands
$id         = isset($id)        ? $id           : NULL; // to enable "script dependsOn id"
$dependsOn  = isset($dependsOn) ? $dependsOn    : NULL; // enables "dependsOn" on "id"
$unique     = isset($unique)    ? $unique       : true; // must be called uncached or with random-parameter to avoid caching by Modx

// Options to pass
$optionsArr = array(
    // Parameters for get()
    'minify'        =>isset($minify)        ? $minify           : true,     // Minify defer script before injecting
    'minifyDefer'   =>isset($minifyDefer)   ? $minifyDefer      : true,     // Minify defer script before injecting
    'minifyCss'     =>isset($minifyCss)     ? $minifyCss        : true,     // Enable minify CSS-files into min.js
    'minifyCssFile' =>isset($minifyCssFile) ? $minifyCssFile    : 'min.css',// FilePath of min.css
    'minifyJs'      =>isset($minifyJs)      ? $minifyJs         : true,     // Enable minify JS-files into min.js
    'minifyJsFile'  =>isset($minifyJsFile)  ? $minifyJsFile     : 'min.js', // FilePath of min.js
    'cache'         =>isset($cache)         ? $cache            : true,     // Enable/disable caching of minified files (for debug)
    'cacheFile'     =>isset($cacheFile)     ? $cacheFile        : 'cache.json', // FilePath for caching latest filetimes
    'hashParam'     =>isset($hashParam)     ? $hashParam        : '',       // Hash xxx xxx.css?h=xosbsof
    'debug'         =>isset($debug)         ? $debug            : false,    // Add debug-infos as HTML-comments, activate console, parse JSON pretty printed
    
    // Internal parameters
    'core_path'     =>$deferMinifyX_base_path,
    'base_path'     =>$modx->getOption('deferMinifyX.base_path',$scriptProperties,MODX_BASE_PATH),
    
    // @todo: enable saving/calling min.css/min.js from different domain
    'assets_path'   =>$modx->getOption('deferMinifyX.assets_path',$scriptProperties,MODX_BASE_PATH),
    'assets_url'    =>$modx->getOption('deferMinifyX.assets_url',$scriptProperties,MODX_BASE_PATH),
);

deferMinifyX::setOptions($optionsArr);

if (isset($addScriptSrc) && !empty(isset($addScriptSrc))) {
    deferMinifyX::addScriptSrc($addScriptSrc, $id, $dependsOn, $unique);
}

if (isset($addScript) && !empty(isset($addScript))) {
    deferMinifyX::addScript($addScript, $id, $dependsOn, $unique);
}

if (isset($addCssSrc) && !empty(isset($addCssSrc))) {
    deferMinifyX::addCssSrc($addCssSrc, $unique);
}

if (isset($get)) {
    $output = deferMinifyX::get($get);
    if (isset($setPlaceholder) && !empty(isset($setPlaceholder)) && $output != '') {
        $modx->setPlaceholder($setPlaceholder, $output);
        return;
    } else {
        return $output;
    }
}