<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: flux_rss.class.php,v 1.7 2021/04/02 13:43:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path;
require_once($base_path."/admin/opac/opac_view/filters/opac_view_filters.class.php");

class flux_rss extends opac_view_filters {
	
	protected function _init_path() {
		$this->path="flux_rss";
	}
    
    public function fetch_data() {
		parent::fetch_data();				
		$myQuery = pmb_mysql_query("SELECT * FROM rss_flux order by nom_rss_flux ");
		$this->liste_item=array();
		$i=0;
		if(pmb_mysql_num_rows($myQuery)){
			while(($r=pmb_mysql_fetch_object($myQuery))) {
				$this->liste_item[$i]=new stdClass();
				$this->liste_item[$i]->id=$r->id_rss_flux ;
				$this->liste_item[$i]->name=$r->nom_rss_flux ;
				if(in_array($r->id_rss_flux ,$this->selected_list))	$this->liste_item[$i]->selected=1;
				else $this->liste_item[$i]->selected=0;				
				$i++;			
			}	
		}
		return true;
 	}
}