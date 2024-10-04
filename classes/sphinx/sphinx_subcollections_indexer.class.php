<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_subcollections_indexer.class.php,v 1.3 2020/04/17 12:50:55 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once $class_path.'/sphinx/sphinx_indexer.class.php';

class sphinx_subcollections_indexer extends sphinx_authorities_indexer {
	
	public function __construct() {
		global $include_path;
		$this->type = AUT_TABLE_SUB_COLLECTIONS;
		$this->default_index = "subcollections";
		parent::__construct();
		$this->setChampBaseFilepath($include_path."/indexation/authorities/subcollections/champs_base.xml");
	}
	
	protected function addSpecificsFilters($id, $filters =array()){
		$filters = parent::addSpecificsFilters($id, $filters);

		//Récupération du statut
		$query = "select num_statut from authorities where id_authority = ".$id." and type_object = ".$this->type;
		$result = pmb_mysql_query($query);
		$row = pmb_mysql_fetch_object($result);
		$filters['multi']['status'] = $row->num_statut;
		return $filters;
	}
}