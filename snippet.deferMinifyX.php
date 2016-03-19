<?php
/**
 * deferMinifyX
 *
 * Flexible all-in-one solution for SEO-tasks like defer JS-, CSS- and IMG-files
 *
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @properties
 * @internal    @modx_category Manager and Admin
 * @internal    @legacy_names deferMinifyX
 * @internal    @installset base
 *
 * @author Deesen / updated: 2016-03-19
 *
 * Latest Updates / Issues on Github : https://github.com/Deesen/deferMinifyX-for-modx-revo
 */
$core_path = $modx->getOption('deferMinifyX.core_path',$scriptProperties,$modx->getOption('core_path').'components/deferMinifyX/');
include_once($core_path.'class/class.deferMinifyX.php');

// $deferMinifyX_base_url = $modx->getOption('site_url', $scriptProperties, $modx->getOption('site_url').'components/deferMinifyX/');

// Commands
$id         = isset($id)        ? $id           : NULL; // to enable "script dependsOn id"
$dependsOn  = isset($dependsOn) ? $dependsOn    : NULL; // enables "dependsOn" on "id"
$unique     = isset($unique)    ? $unique       : true; // must be called uncached or with random-parameter to avoid caching by Modx

// Options to pass
$optionsArr = array(
    // Parameters for get()
    'deferImages'   =>isset($deferImages)   ? $deferImages      : false,    // Add deferImages-script (src="blank.jpg" data-src=""
    'minify'        =>isset($minify)        ? $minify           : true,     // Enable/disable minify globally
    'minifyDefer'   =>isset($minifyDefer)   ? $minifyDefer      : true,     // Minify defer script before injecting
    'minifyCss'     =>isset($minifyCss)     ? $minifyCss        : true,     // Enable minify CSS-files into min.js
    'minifyCssFile' =>isset($minifyCssFile) ? $minifyCssFile    : 'min.css',// FilePath of min.css
    'minifyJs'      =>isset($minifyJs)      ? $minifyJs         : true,     // Enable minify JS-files into min.js
    'minifyJsFile'  =>isset($minifyJsFile)  ? $minifyJsFile     : 'min.js', // FilePath of min.js
    'cache'         =>isset($cache)         ? $cache            : true,     // Enable/disable caching of minified files (for debug)
    'cacheFile'     =>isset($cacheFile)     ? $cacheFile        : 'deferMinifyX.json', // FilePath for caching latest filetimes
    'hashParam'     =>isset($hashParam)     ? $hashParam        : '',       // Hash xxx xxx.css?h=xosbsof
    'debug'         =>isset($debug)         ? $debug            : false,    // Add debug-infos
    'debugTpl'      =>isset($debug)         ? $debug            : 'default',// @todo: 'default' shows infos as HTML-comments but can be styled 

    // Internal parameters
    'core_path'     =>$core_path,
    'base_path'     =>$modx->getOption('deferMinifyX.base_path',$scriptProperties,MODX_BASE_PATH), 
    'cache_path'    =>isset($cachePath)     ? $cachePath        : MODX_BASE_PATH,  // Path to store cacheFile

    // @todo: enable saving/calling min.css/min.js from different domain
    // $modx->getOption('deferMinifyX.assets_path',$scriptProperties,MODX_BASE_PATH)
    'assets_path'   =>isset($assetsPath)    ? $assetsPath       : false,    // Save minified files into different directory
    'assets_url'    =>isset($debug)         ? $debug            : '',       // Use absolute URL for loading minified files from different domain 

    // Determine if session is allowed for debugging-infos
    'sessionAuth'   =>$modx->user->hasSessionContext('mgr')
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
    $outputArr = deferMinifyX::get($get);

    if (isset($setPlaceholder) && !empty(isset($setPlaceholder)) && $outputArr['output'] != '') {
        $modx->setPlaceholder($setPlaceholder, $outputArr['output'].$outputArr['debug']);
    } else {
        return $outputArr['output'].$outputArr['debug'];
    }
}

return '';