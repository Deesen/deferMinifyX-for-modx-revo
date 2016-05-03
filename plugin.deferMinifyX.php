/**
* deferMinifyX
*
* Flexible all-in-one solution for SEO-tasks like defer JS-, CSS- and IMG-files
*
* @category    plugin
* @version     0.2
* @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
* @internal    @events OnLoadWebDocument,OnWebPagePrerender
* @internal    @modx_category Manager and Admin
* @internal    @legacy_names deferMinifyX
* @internal    @installset base
*
* @author Deesen / updated: 2016-05-02
*
* Latest Updates / Issues on Github : https://github.com/Deesen/deferMinifyX-for-modx-revo
*/
$core_path = $modx->getOption('deferMinifyX.core_path', $scriptProperties, $modx->getOption('core_path').'components/deferMinifyX/');

require($core_path."plugin.deferMinifyX.inc.php");