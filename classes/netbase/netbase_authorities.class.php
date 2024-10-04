<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: netbase_authorities.class.php,v 1.2.6.4 2024/03/27 10:47:09 dgoron Exp $
if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once ($class_path . "/indexations_collection.class.php");

class netbase_authorities {
    
    protected static $object_type = 0;
    
    protected static $indexation_authorities = [];
    
    public static function index_from_query($query, $object_type=0) {
        $result = pmb_mysql_query($query);
        $nb_indexed = pmb_mysql_num_rows($result);
        if ($nb_indexed) {
            $indexation_authority = indexations_collection::get_indexation($object_type);
            $indexation_authority->set_deleted_index(true);
            authorities_collection::setOptimizer(authorities_collection::OPTIMIZE_MEMORY);
            while (($row = pmb_mysql_fetch_object($result))) {
                $indexation_authority->maj($row->id);
            }
            pmb_mysql_free_result($result);
        }
        return $nb_indexed;
    }
    
    public static function raz_index() {
        $indexation_authorities = static::get_indexation_authorities();
        $indexation_authorities->raz_fields_table();
        $indexation_authorities->raz_words_table();
        if(empty(static::$object_type)) {
            $indexation_authorities->disable_fields_table_keys();
            $indexation_authorities->disable_words_table_keys();
        }
    }
    
    public static function index() {
        $indexation_authorities = static::get_indexation_authorities();
		$indexation_authorities->launch_indexation();
    }
    
    public static function index_by_step($step=0) {
        $indexation_authorities = static::get_indexation_authorities();
    	if($step == 0) {
    		netbase_entities::clean_files($indexation_authorities->get_directory_files());
    	}
    	return $indexation_authorities->maj_by_step($step);
    }
    
    public static function import_bdd() {
        $indexation_authorities = static::get_indexation_authorities();
        $indexation_authorities->maj_bdd_from_files();
    }
    
    public static function enable_index() {
        $indexation_authorities = static::get_indexation_authorities();
        $indexation_authorities->enable_fields_table_keys();
        $indexation_authorities->enable_words_table_keys();
    }
    
    public static function get_nb_steps() {
        return count(indexation_authorities::$steps);
    }
    
    public static function set_object_type($object_type) {
        static::$object_type = $object_type;
    }
    
    public static function get_indexation_authorities() {
        if(!isset(static::$indexation_authorities[static::$object_type])) {
            static::$indexation_authorities[static::$object_type] = new indexation_authorities(indexations_collection::get_xml_file_path(static::$object_type), "authorities", static::$object_type);
        }
        return static::$indexation_authorities[static::$object_type];
    }
} // fin de déclaration de la classe netbase