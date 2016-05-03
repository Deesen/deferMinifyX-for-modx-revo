<?php
if (!defined('MODX_BASE_PATH')) { die('What are you doing? Get out of here!'); }

$core_path = $modx->getOption('deferMinifyX.core_path', $scriptProperties, $modx->getOption('core_path') . 'components/deferMinifyX/');
require_once($core_path . 'class/class.deferMinifyX.php');

// Helpers for translating configuration
if(!function_exists('bool')) {
    function bool($val) { return $val == true ? true : false; }
}

// Options to pass
$optionsArr = array(
// Parameters for get()
'context'       =>isset($context)       ? $context          : 'web',    // Comma-separated list of contexts to apply plugin to
'defer'         =>isset($defer)         ? bool($defer)      : false,    // Enable/disable minify globally
'minify'        =>isset($minify)        ? bool($minify)     : true,     // Enable/disable minify globally
'minifyDefer'   =>isset($minifyDefer)   ? bool($minifyDefer): true,     // Minify defer script before injecting
'minifyCss'     =>isset($minifyCss)     ? bool($minifyCss)  : true,     // Enable minify CSS-files into min.css
'minifyCssFile' =>isset($minifyCssFile) ? $minifyCssFile    : 'min.css',// FilePath of min.css
'minifyJs'      =>isset($minifyJs)      ? bool($minifyJs)   : true,     // Enable minify JS-files into min.js
'minifyJsFile'  =>isset($minifyJsFile)  ? $minifyJsFile     : 'min.js', // FilePath of min.js
'minifyJsLib'   =>isset($minifyJsLib)   ? $minifyJsLib      : 'minify', // Only jsshrink available right now
'minifyCssLib'  =>isset($minifyCssLib)  ? $minifyCssLib     : 'minify', // Only jsshrink available right now
'minifyHtml'    =>isset($minifyHtml)    ? $minifyHtml       : 'disabled', // Minify HTML-Output
'noScriptCss'   =>isset($noScriptCss)   ? $noScriptCss      : true,     // Add <noscript><link style.css></noscript> as fallback
'deferImages'   =>isset($deferImages)   ? bool($deferImages): false,    // Add deferImages-script (src="blank.jpg" data-src="real-image.jpg")
'blankImage'    =>isset($blankImage)    ? $blankImage       : 'img/blank.jpg', // Name of blank.jpg
'cache'         =>isset($cache)         ? bool($cache)      : true,     // Enable/disable caching of minified files (for debug)
'hashParam'     =>isset($hashParam)     ? $hashParam        : '',       // Hash xxx xxx.css?h=xosbsof
'debug'         =>isset($debug)         ? bool($debug)      : false,    // Add debug-infos

// Internal parameters
'core_path'     =>$core_path,       // Path to snippet.deferMinifyX.php
'base_path'     =>MODX_BASE_PATH,   // Base path
'cache_path'    =>!empty($cachePath)    ? $cachePath        : $modx->getOption('core_path').'cache/deferMinifyX/', // Path to store internal cache-files
// 'assets_path'   =>isset($assetsPath)    ? $assetsPath       : '',       // Save minified files into different directory
// 'assets_url'    =>isset($assetsUrl)     ? $assetsUrl        : '',       // Use absolute URL for loading minified files from different domain
'defaultCssFiles'=>isset($defaultCssFiles) ? $defaultCssFiles : '',
'defaultJsFiles'=>isset($defaultJsFiles) ? $defaultJsFiles : '',
'cacheReset'=>isset($cacheReset) ? $cacheReset : 'index',
'sessionAuth'   =>$modx->user->hasSessionContext('mgr')   // Determine if session is allowed for debugging-infos
);

if(!in_array($modx->context->key, explode(',', $context))) return;

// Check activeIds
if(!empty($activeIds)) {
    $exp = explode(',', $activeIds);
    if(!in_array($modx->resource->get('id'), $exp)) return;
}

// Check inactiveIds
if(!empty($inactiveIds)) {
    $exp = explode(',', $inactiveIds);
    if(in_array($modx->resource->get('id'), $exp)) return;
}

$e = $modx->event;
switch ($e->name) {

    // 1. Set options at Modx Init before cache
    case "OnLoadWebDocument":
        deferMinifyX::setOptions($optionsArr);
        deferMinifyX::loadCache();
        deferMinifyX::prepareMinifyLibs();
        
        // default min-sets from plugin-config
        deferMinifyX::addCssSrc($defaultCssFiles, true, false, false, 'default'); // Add css as default-set
        deferMinifyX::addScriptSrc($defaultJsFiles, 'min');     // Add script-src with ID "min"
        if(!empty($defaultInlineJsChunk)) {
            deferMinifyX::addScript($modx->getChunk($defaultInlineJsChunk)); // Add inline-script
        }
        break;

    // 2. Before sending to browser prepare and prepend final script to </body> 
    case "OnWebPagePrerender":

        // Optional debugTpl with [+ids+],[+js+],[+css+],[+options+],[+messages+]
        if(!empty($debugTpl)) {
            $debugTpl = $modx->getChunk($debugTpl);
            deferMinifyX::setDebugTpl($debugTpl);
        }
        
        if (isset($setPlaceholder) && !empty(isset($setPlaceholder)) && $outputArr['output'] != '') {
            $outputArr = deferMinifyX::getDefer();
            $modx->setPlaceholder($setPlaceholder, $outputArr['output'].$outputArr['debug']);
            break;
        } else {
            $output = &$modx->resource->_output;
            $output = deferMinifyX::modifyOutput($output);
        }
        deferMinifyX::updateCache();
        break;

    // Clear cache
    case "OnSiteRefresh":
        deferMinifyX::setOptions($optionsArr);
        deferMinifyX::resetCache();
        break;

    // Important! Stop here!
    default :
        return;
}
