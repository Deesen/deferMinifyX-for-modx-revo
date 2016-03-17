<?php
/**
 * deferMinifyX
 
 * @version     0.1 alpha
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Deesen / updated: 2016-03-17
 *
 * Latest Updates / Issues on Github : 
 */

class deferMinifyX
{
    static $jsArr = array();
    static $cssArr = array();
    static $options = array();
    static $debugMessages = array();
    private static $version = '0.1';

    static function addScriptSrc($string, $id=NULL, $dependsOn=NULL, $unique=true)
    {
        $arr = explode(',', $string);
        $id         = !$id || count($arr)   ? NULL  : $id;
        $dependsOn  = !$dependsOn           ? '0'   : $dependsOn;
        
        foreach($arr as $value) {
            $found = $unique != false ? self::checkUniqueJs($value, 'src') : false;
            if (!$found) {
                $newSrc = array();
                $newSrc['val'] = $value;
                if ($id) $newSrc['id'] = $id;
                self::$jsArr[$dependsOn]['src'][] = $newSrc;
            }
        }
    }

    static function addScript($string, $id, $dependsOn, $unique=true)
    {
        $arr = explode(',', $string);
        $id         = !$id || count($arr)   ? NULL  : $id;
        $dependsOn  = !$dependsOn           ? '0'   : $dependsOn;

        foreach($arr as $value) {
            $found = $unique != false ? self::checkUniqueJs($value, 'js') : false;
            if (!$found) {
                $newSrc = array();
                $newSrc['val'] = $value;
                if ($id) $newSrc['id'] = $id;
                self::$jsArr[$dependsOn]['js'][] = $newSrc;
            }
        }
    }

    static function checkUniqueJs($value, $type)
    {
        $found = false;
        foreach(self::$jsArr as $dependsOn=>$set) {
            if(isset($set[$type])) {
                foreach ($set[$type] as $script) {
                    if($script['val'] === $value) {
                        $found = true;
                        break;
                    }
                }
            }
        }
        return $found;
    }

    static function addCssSrc($string, $unique)
    {
        $arr = explode(',', $string);
        
        foreach($arr as $src) {
            $found = false;
            if ($unique != false) {
                foreach (self::$cssArr as $css) {
                    if ($css['src'] === $src) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) self::$cssArr[]['src'] = $src;
        }
    }
   
    static function setOptions($optionsArr)
    {
        self::$options = $optionsArr;
    }
    static function getOption($option)
    {
        return isset(self::$options[$option]) ? self::$options[$option] : NULL;
    }

    static function debug($message)
    {
        self::$debugMessages[] = $message;
    }

    static function getVersion()
    {
        return self::$version;
    }

    static function get($mode)
    {
        $core_path = self::getOption('core_path');
        $jsonIndent = self::getOption('debug') != false ? JSON_PRETTY_PRINT : 0;
        $cache = array('css'=>0,'js'=>0);
        $cssChanged = false;
        $jsChanged = false;
        $minCss = self::getOption('minifyCssFile');
        $minFile = self::getOption('minifyJsFile');

        // Prepare minify ifi enabled
        if(self::getOption('minifyDefer') || self::getOption('minifyCss') || self::getOption('minifyJs') || self::getOption('minifyJsScript')) {
            require_once($core_path.'class/JShrink.php');    // Load JShrink -> https://github.com/tedious/JShrink
            $cached = file_get_contents(self::getOption('cacheFile'));
            $cached = json_decode($cache,true);
            $cache  = json_last_error() == JSON_ERROR_NONE ? $cached : $cache;
            $cache['css'] = isset($cache['css']) ? $cache['css'] : 0;
            $cache['js'] = isset($cache['js']) ? $cache['js'] : 0;
        }

        // Prepare CSS-object
        if(self::getOption('minifyCss')) {

            // Check for changed files
            if(!empty(self::$cssArr)) {
                foreach (self::$cssArr as $file) {
                    $time = filemtime($file['src']);       // @todo: Determine / use doc_root ?
                    if ($time >> $cache['css']) $cssChanged = true; // Dont break, find newest
                    $cache['css'] = $time >> $cache['css'] ? $time : $cache['css'];
                }
            }

            // Create new min.css
            if($cssChanged || !file_exists($minCss)) {

                // Buffer Css-files
                $buffer = '';
                foreach (self::$cssArr as $file) {
                    $fileContent = file_get_contents($file['src']);    // @todo: Determine / use doc_root ? 
                    $buffer .= $fileContent;
                }

                $buffer = \JShrink\Minifier::minify($buffer);

                if(!file_put_contents($minCss, $buffer)) self::debug('Minified Css-File could not be written: '.$minCss);
                if(!file_put_contents(self::getOption('cacheFile'), json_encode($cache))) self::debug('Cache-File could not be written: '.self::getOption('cacheFile'));
            }

            // Overwrite $cssArr - set min.css as only file
            self::$cssArr[0]['src'] = $minCss.'?'.self::getOption('hashParam').$cache['css'];

        }

        // Prepare JS-object
        if (self::getOption('minifyJs')) {

            // Check for changed files
            foreach (self::$jsArr as $dependsOn=>$arr) {
                if($dependsOn === 'min') continue;       // Ignore file that dependsOn "min"
                if(isset($arr['src'])) {
                    foreach ($arr['src'] as $scriptSrc) {
                        $filePath = $scriptSrc['val'];
                        if(file_exists($filePath)) {
                            $time = filemtime($filePath);       // @todo: Determine / use doc_root ?
                            if ($time >> $cache['js']) $jsChanged = true; // Dont break, find newest
                            $cache['js'] = $time >> $cache['js'] ? $time : $cache['js'];
                        }
                    }
                }
            }

            // Create new min.js
            if($jsChanged || !file_exists($minFile) || !self::getOption('cache')) {

                // @todo: assure order of scripts is correct acc to "dependsOn" and "id" ?

                $buffer = '';

                // Buffer scriptSrc first
                foreach (self::$jsArr as $dependsOn => $arr) {
                    if ($dependsOn === 'min') continue;       // Ignore files that dependsOn "min"
                    if (isset($arr['src'])) {
                        foreach ($arr['src'] as $scriptSrc) {
                            $filePath = $scriptSrc['val'];
                            if (file_exists($filePath)) {
                                $fileContent = file_get_contents($filePath);    // @todo: Determine / use doc_root ? 
                                $buffer .= $fileContent;
                            }
                        }
                    }
                }

                // Now buffer scripts
                if (self::getOption('minifyJsScript')) {
                    foreach (self::$jsArr as $dependsOn => $jsArr) {
                        if ($dependsOn === 'min') continue;       // Ignore files that dependsOn "min"
                        if (isset($jsArr['js'])) {
                            foreach ($jsArr['js'] as $script) {
                                $buffer .= $script['val'];
                            }
                        }
                    }
                }

                $buffer = self::getOption('minify') ? \JShrink\Minifier::minify($buffer) : $buffer;
                if(!file_put_contents($minFile, $buffer)) self::debug('Minified Js-File could not be written: '.$minFile);
                unset($buffer);
            }
            
            // @todo: Add inject inline-mode

            // Overwrite $cssArr - set min.css as only file - append dependsOn min
            $keepDependsOnMinSrc = isset(self::$jsArr['min']) ? array('min'=>self::$jsArr['min']) : array();
            self::$jsArr[0]['src'][0]['val']    = $minFile.'?'.self::getOption('hashParam').$cache['js'];
            self::$jsArr[0]['src'][0]['id']     = 'min';
            self::$jsArr = !empty($keepDependsOnMinSrc) ? array_merge(self::$jsArr, $keepDependsOnMinSrc) : self::$jsArr;
        };

        // Write cache-file
        if($cssChanged || $jsChanged) {
            if (!file_put_contents(self::getOption('cacheFile'), json_encode($cache))) self::debug('Cache-File could not be written: ' . self::getOption('cacheFile'));
        }

        // Prepare CSS-object
        $cssStr = json_encode(self::$cssArr);

        // Prepare JS-object            
        $scriptSrcStr = json_encode(self::$jsArr, $jsonIndent);

        // Get JS-chaining magic
        $output = self::getJsFunctions($cssStr, $scriptSrcStr);

        // Minify final defer call
        // @todo: Cache already minified
        if(self::getOption('minifyDefer') && self::getOption('minify')) {
            $output = \JShrink\Minifier::minify($output);
        }
        
        return $output . self::renderDebugMsg();
    }
    
    // JS-function to provide multi-dimensional dependence of defered script-srcs and scripts
    static function getJsFunctions($cssStr, $scriptSrcStr) {
        return "
    <script>
        try {
            var css = {$cssStr};
            var js = {$scriptSrcStr};
    
            var element = {}; var l = {}; var p = 0; var c = 0; var cx = 0; var id = 0; var done = false;
            
            function deferCssSrc() {
                // Process Css-files first
                if(css.length) {
                    for (c = 0; c < css.length; c++) {
                        l = document.createElement('link');
                        l.rel = 'stylesheet';
                        l.href = css[c]['src'];
                        h = document.getElementsByTagName('head')[0];
                        h.parentNode.insertBefore(l, h);
                        " . self::console('css_added') . "
                    }
                }
            }
                
            function deferScriptSrc(p) {
                if (js[p].hasOwnProperty('src')) {
                    for (c in js[p]['src']) {
                        id = js[p]['src'][c]['id'] != undefined ? js[p]['src'][c]['id'] : 'src_'+p+'_'+c;
                        val = js[p]['src'][c]['val'];
                        element[id] = document.createElement('script');
                        element[id].src = val;
                        document.body.appendChild(element[id]);
                        " . self::console('script_src_added_with_id') . "
                        addOnLoadHandler(id);
                    }
                }
            }
            
            function deferScript(p) {
                if (js[p].hasOwnProperty('js')) {
                    for (c in js[p]['js']) {
                        id = js[p]['js'][c]['id'] != undefined ? js[p]['js'][c]['id'] : 'js_'+p+'_'+c;
                        val = js[p]['js'][c]['val'];
                        element[id] = document.createElement('script');
                        element[id].text = val;
                        document.body.appendChild(element[id]);
                        " . self::console('script_added_with_id') . "
                    }
                }
            }
            
            function deferRecursive(p) {
                if (js.hasOwnProperty(p)) {
                    if (js[p].hasOwnProperty('src') || js[p].hasOwnProperty('js')) {
                        deferScriptSrc(p);
                        deferScript(p);
                    }
                }
            }
            
            function addOnLoadHandler(id) {
                if(element[id] == undefined) {
                    " . self::console('element_undefined_id') . "
                } else {
                    if (js.hasOwnProperty(id)) {
                        if(js[id].hasOwnProperty('src') || js[id].hasOwnProperty('js')) {
                            " . self::console('recursive_chain_for_id') . "
                            element[id].onload=element[id].onreadystatechange = function() {
                                if ( !done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete') ) {
                                    " . self::console('process_scripts_with_dependson_id') . "
                                    deferRecursive(id);
                                }
                            }
                        }
                    }
                }
            }
            
            function downloadDeferedAtOnload() {
                " . self::console('init') . self::console('log_cssSrc') . self::console('log_js') . "
                deferCssSrc();
                
                // Process scripts-src without dependsOn first to add JS-libraries like jQuery
                if(typeof js['0'] == 'object') {
                    deferRecursive('0');
                }
            }
            
            if (window.addEventListener)
                window.addEventListener('load', downloadDeferedAtOnload, false);
            else if (window.attachEvent)
                window.attachEvent('onload', downloadDeferedAtOnload);
            else window.onload = downloadDeferedAtOnload;
        } catch(e) {
            console.log(e);
        }
    </script>";
    }

    // Prepare console logs for debugging
    static function console($key)
    {
        global $modx;

        // @todo: check/clean messages-block ?

        if($modx->user->hasSessionContext('mgr') && self::getOption('debug')) {
            $c = '';
            switch($key) {
                case 'init':
                    $c = "'deferMinifyX v".self::getVersion()." - onLoad fired - Debug messages enabled, disable for production!'"; $a = "warn"; break;
                case 'log_cssSrc':
                    $c = "'var css = ',css"; $a = "info"; break;
                case 'log_js':
                    $c = "'var js = ', js"; $a = "info"; break;
                case 'css_added':
                    $c = "'CSS defered '+l.href"; $a = "log"; break;
                case 'process_script_without':
                    $c = "'process scripts[0] (without dependsOn)'"; $a = "log"; break;
                case 'recursive_chain_for_id':
                    $c = "'recursive chaining \"'+id+'\"'"; $a = "log"; break;
                case 'set_onload_script_src_for_p':
                    $c = "'set onLoad js for id \"'+p+'\"'"; $a = "log"; break;
                case 'id_loaded_process_js':
                    $c = "'\"'+p+'\" loaded, process js with dependsOn \"'+p+'\"'"; $a = "log"; break;
                case 'script_src_added_with_id':
                    $c = "'js['+p+'] \"'+val+'\" added with id \"'+id+'\"'"; $a = "log"; break;
                case 'process_scripts_with_dependson_id':
                    $c = "'process Scripts with dependsOn \"'+id+'\"'"; $a = "log"; break;
                case 'script_added_with_id':
                    $c = "'script added with id \"'+id+'\"'"; $a = "log"; break;
                case 'element_undefined_p':
                    $c = "'! element \"'+p+'\" undefined'"; $a = "error"; break;
                case 'element_undefined_id':
                    $c = "'! element \"'+id+'\" undefined'"; $a = "error"; break;
                default:
                    $c = "'Debug: \"{$key}\"'"; $a = "info"; break;
            }
            return "console.{$a}({$c});";
        };
        return '';
    }

    static function renderDebugMsg()
    {
        global $modx;

        // ADD DEBUG-INFO AS HTML-COMMENTS ONLY WHEN LOGGED IN
        if ($modx->user->hasSessionContext('mgr') && self::getOption('debug')) {
            return '
<!-- deferMinifyX Debug:
##################################################################################
Items in $jsArr[dependsOn]:
' . print_r(self::$jsArr, true) . '
##################################################################################
Items in $cssArr:
' . print_r(self::$cssArr, true) . '
##################################################################################
$options:
' . print_r(self::$options, true) . '
##################################################################################
$scriptProperties:
' . (isset($scriptProperties) ? print_r($scriptProperties, true) : 'Not set') . '
##################################################################################
Debug-Messages:
 - ' . join("\n - ", self::$debugMessages) . '
##################################################### /deferMinifyX Debug -->
';
        };
        return '';
    }
}