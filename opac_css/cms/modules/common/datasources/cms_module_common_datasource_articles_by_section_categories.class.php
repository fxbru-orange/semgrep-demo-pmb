<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_articles_by_section_categories.class.php,v 1.6 2022/09/06 07:52:19 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_articles_by_section_categories extends cms_module_common_datasource_list{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->sortable = true;
		$this->limitable = true;
		$this->paging = true;
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_section",
			"cms_module_common_selector_env_var",
		);
	}

	/*
	 * On d�fini les crit�res de tri utilisable pour cette source de donn�e
	 */
	protected function get_sort_criterias() {
		return array (
			"publication_date",
			"id_article",
			"article_title",
			"article_order",
		    "rand()"
		);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		$selector = $this->get_selected_selector();
		if ($selector) {
			if(!isset($this->parameters['operator_between_authorities'])) $this->parameters['operator_between_authorities'] = 'or';
			switch ($this->parameters["operator_between_authorities"]) {
				case 'and':
					if($this->parameters['autopostage']){
						$query = "select distinct cms_sections_descriptors.num_noeud
						from cms_sections_descriptors
						join noeuds as section_noeuds on section_noeuds.id_noeud = cms_sections_descriptors.num_noeud
						join noeuds as categ_noeuds on categ_noeuds.path like concat(section_noeuds.path,'%') and section_noeuds.id_noeud != categ_noeuds.id_noeud
						where cms_sections_descriptors.num_section='".($selector->get_value()*1)."'";
					} else {
						$query = "select distinct cms_sections_descriptors.num_noeud
						from cms_sections_descriptors
					    where cms_sections_descriptors.num_section = '".($selector->get_value()*1)."'";
					}
					$result = pmb_mysql_query($query);
					$descriptors = array();
					if($result && (pmb_mysql_num_rows($result) > 0)){
						while($row = pmb_mysql_fetch_object($result)){
							$descriptors[] = $row->num_noeud;
						}
					}
					if(count($descriptors)) {
						$query = "select id_article,if(article_start_date != '0000-00-00 00:00:00',article_start_date,article_creation_date) as publication_date
						from cms_articles join cms_articles_descriptors on id_article=num_article
						where cms_articles_descriptors.num_noeud IN (".implode(',', $descriptors).")
						group by id_article
						having count(id_article) = ".count($descriptors);
						if ($this->parameters["sort_by"] != "") {
							$query .= " order by ".$this->parameters["sort_by"];
							if ($this->parameters["sort_order"] != "") $query .= " ".$this->parameters["sort_order"];
						}
						$result = pmb_mysql_query($query);
					} else {
						$result = false;
					}
					break;
				case 'or':
				default:
				    if($this->parameters['autopostage']){
				        $query ='select id_article,if(article_start_date != "0000-00-00 00:00:00",article_start_date,article_creation_date) as publication_date
		                from cms_sections_descriptors
		                join noeuds as articles_noeuds on articles_noeuds.id_noeud = cms_sections_descriptors.num_noeud
		                join noeuds as categ_noeuds on categ_noeuds.path like concat(articles_noeuds.path,"%") and articles_noeuds.id_noeud != categ_noeuds.id_noeud
		                join cms_articles_descriptors as cmd on categ_noeuds.id_noeud = cmd.num_noeud
		                join cms_articles on cmd.num_article = id_article
		                where cms_sections_descriptors.num_section='.($selector->get_value()*1).' group by id_article';
				    }else{
				        $query = "select distinct id_article,if(article_start_date != '0000-00-00 00:00:00',article_start_date,article_creation_date) as publication_date 
				   
							from cms_articles 
							join cms_articles_descriptors on id_article=num_article 
							join cms_sections_descriptors on cms_articles_descriptors.num_noeud=cms_sections_descriptors.num_noeud 
						    where cms_sections_descriptors.num_section = '".($selector->get_value()*1)."'";
				    }
					if ($this->parameters["sort_by"] != "") {
						$query .= " order by ".$this->parameters["sort_by"];
						if ($this->parameters["sort_order"] != "") $query .= " ".$this->parameters["sort_order"];
					}
					$result = pmb_mysql_query($query);
					break;
			}
			$return = array();
			if($result && pmb_mysql_num_rows($result) > 0){
				while($row = pmb_mysql_fetch_object($result)){
				    $articles[] = $row->id_article;
				}
			}
			$return["articles"] = $this->filter_datas("articles",$articles);

			if ($this->paging && isset($this->parameters['paging_activate']) && $this->parameters['paging_activate'] == "on") {
			    $return["paging"] = $this->inject_paginator($return['articles']);
			    $return['articles'] = $this->cut_paging_list($return['articles'], $return["paging"]);
			} else if ($this->parameters["nb_max_elements"] > 0) {
			    $return["articles"] = array_slice($return["articles"], 0, $this->parameters["nb_max_elements"]);
			}
			
			return $return;
		}
		return false;
	}
}