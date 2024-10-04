<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: navbar.inc.php,v 1.31 2022/10/11 09:32:37 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php"))
    die("no access");

use Pmb\Common\Views\NavbarView;

global $base_path;
require_once ($base_path . '/includes/javascript/form.inc.php');

function getNavbar($page, $nbr_lignes, $nb_per_page, $url, $nb_per_page_custom_url = "&nb_per_page_custom=!!nb_per_page_custom!!", $action = ''){
    global $opac_items_pagination_custom, $cms_active;
    // on fait suivre les variables d'environnement du portail
    
    if ($cms_active && strpos($url, 'javascript:') === false) {
        $query = "select distinct var_name from cms_vars";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                $var_name = $row->var_name;
                global ${$var_name};
                if (! empty(${$var_name}) && strpos($url, $var_name."=") === false) {
                    $url .= "&" . $var_name . "=" . rawurlencode(${$var_name});
                }
            }
        }
    }
    $navbar = new NavbarView($page, $nbr_lignes, $nb_per_page, $url, $nb_per_page_custom_url, $action);
    $navbar->setCustoms($opac_items_pagination_custom);
    return $navbar;
}

function printnavbar($page, $nbr_lignes, $nb_per_page, $url, $nb_per_page_custom_url = "&nb_per_page_custom=!!nb_per_page_custom!!", $action = '')
{
    $navbar = getNavbar($page, $nbr_lignes, $nb_per_page, $url, $nb_per_page_custom_url, $action);
    return $navbar->render();
}