<?php
/**
 * deferMinifyX
 *
 * Flexible all-in-one solution for SEO-tasks like defer JS-, CSS- and IMG-files
 *
 * @category    snippet
 * @version     0.2
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @properties 
 * @internal    @modx_category Manager and Admin
 * @internal    @legacy_names deferMinifyX
 * @internal    @installset base
 *
 * @author Deesen / updated: 2016-03-25
 *
 * Latest Updates / Issues on Github : https://github.com/Deesen/deferMinifyX-for-modx-revo
 */
$core_path = $modx->getOption('deferMinifyX.core_path', $scriptProperties, $modx->getOption('core_path').'components/deferMinifyX/');

return require($core_path."snippet.deferMinifyX.inc.php");
?>