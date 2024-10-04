<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_common_datatype_file_ui.class.php,v 1.8 2021/07/26 13:55:39 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


/**
 * class onto_common_datatype_small_text_ui
 * 
 */
class onto_common_datatype_file_ui extends onto_common_datatype_ui {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/


	/**
	 * 
	 *
	 * @param property property la propri�t� concern�e
	 * @param restriction $restrictions le tableau des restrictions associ�es � la propri�t� 
	 * @param array datas le tableau des datatypes
	 * @param string instance_name nom de l'instance
	 * @param string flag Flag

	 * @return string
	 * @static
	 * @access public
	 */
	public static function get_form($item_uri,$property, $restrictions,$datas, $instance_name,$flag) {
		global $msg,$charset,$ontology_tpl;
		
		$form=$ontology_tpl['form_row'];
		$form=str_replace("!!onto_row_label!!", htmlentities(encoding_normalize::charset_normalize($property->get_label(), 'utf-8') ,ENT_QUOTES,$charset), $form);
		
		$content='';
		if(!empty($datas) && sizeof($datas)){
			$i=1;
			$first=true;
			$new_element_order=max(array_keys($datas));
			
			$form=str_replace("!!onto_new_order!!",$new_element_order , $form);
			
			foreach($datas as $key=>$data){
				$row=$ontology_tpl['form_row_content'];
				
				if($data->get_order()){
					$order=$data->get_order();
				}else{
					$order=$key;
				}
				$inside_row=$ontology_tpl['form_row_content_file'];
				$inside_row .= $ontology_tpl['form_row_content_type'];
				//test sur le nom du pr�c�dent fichier upload�
				//par d�faut $data->get_value() est un tableau
				//� voir si cela est n�cessaire
				if ($data->get_value() && !is_array($data->get_value())) {
					$inside_row=str_replace("!!onto_contribution_last_file!!", $ontology_tpl['form_row_content_last_file'],$inside_row);
					$inside_row=str_replace("!!onto_row_content_file_value!!", $data->get_value(),$inside_row);
				} else {
					$inside_row=str_replace("!!onto_contribution_last_file!!","",$inside_row);
					$inside_row=str_replace("!!onto_row_content_file_value!!","",$inside_row);
				}
				$inside_row=str_replace("!!onto_row_content_range!!",$property->range[0] , $inside_row);
				
				$row=str_replace("!!onto_inside_row!!",$inside_row , $row);
				
				$input='';
				if($first){
					if($restrictions->get_max()<$i || $restrictions->get_max()===-1){
						$input=$ontology_tpl['form_row_content_input_add'];
					}
				}else{
					$input=$ontology_tpl['form_row_content_input_del'];
				}
				
				$row=str_replace("!!onto_row_inputs!!",$input , $row);
				$row=str_replace("!!onto_row_order!!",$order , $row);
				
				$content.=$row;
				$first=false;
				$i++;
			}
		}else{
			$form=str_replace("!!onto_new_order!!","0" , $form);
			
			$row=$ontology_tpl['form_row_content'];
			
			$inside_row=$ontology_tpl['form_row_content_file'];
			$inside_row .= $ontology_tpl['form_row_content_type'];

			$inside_row=str_replace("!!onto_contribution_last_file!!", '',$inside_row);
			$inside_row=str_replace("!!onto_row_content_file_value!!","",$inside_row);
			$inside_row=str_replace("!!onto_row_content_range!!",$property->range[0] , $inside_row);
			
			$row=str_replace("!!onto_inside_row!!",$inside_row , $row);
			$input='';
			if($restrictions->get_max()!=1){
				$input=$ontology_tpl['form_row_content_input_add'];
			}
			$row=str_replace("!!onto_row_inputs!!",$input , $row);
			
			$row=str_replace("!!onto_row_order!!","0" , $row);
			
			$content.=$row;
		}
		
		$form = str_replace("!!onto_rows!!", $content, $form);
		$form = str_replace("!!onto_row_scripts!!", static::get_scripts(), $form);
		$form = self::get_form_with_special_properties($property, $datas, $instance_name, $form);
		$form = str_replace("!!onto_row_id!!", $instance_name.'_'.$property->pmb_name, $form);
		
		return $form;
	} // end of member function get_form

	/**
	 * 
	 *
	 * @param onto_common_datatype datas Tableau des valeurs � afficher associ�es � la propri�t�
	 * @param property property la propri�t� � utiliser
	 * @param string instance_name nom de l'instance
	 * 
	 * @return string
	 * @access public
	 */
	public function get_display($datas, $property, $instance_name) {
		
		$display='<div id="'.$instance_name.'_'.$property->pmb_name.'">';
		$display.='<p>';
		$display.=$property->get_label().' : ';
		foreach($datas as $data){
			$display.=$data->get_formated_value();
		}
		$display.='</p>';
		$display.='</div>';
		return $display;
		
	} // end of member function get_display
	
	
	/**
	 * Retourne un object JSON avec 2 m�thodes check et get_error_message
	 *
	 * @param onto_property property la propri�t� concern�e
	 * @param onto_restriction $restrictions le tableau des restrictions associ�es � la propri�t�
	 * @param array datas le tableau des datatypes
	 * @param string instance_uri URI de l'instance
	 * @param string flag Flag
	 *
	 * @return string
	 * @static
	 * @access public
	 */
	public static function get_validation_js($item_uri,$property, $restrictions,$datas, $instance_name,$flag){
	    global $msg;
	    
	    return '{
			"message": "'.addslashes($property->get_label()).'",
			"valid" : true,
			"nb_values": 0,
			"error": "",
			"values": new Array(),
			"check": function(){
				this.values = new Array();
				this.nb_values = 0;
				this.valid = true;
				var nodeOrder = document.getElementById("'.$instance_name.'_'.$property->pmb_name.'_new_order");
                if (nodeOrder) {
                    var order = nodeOrder.value;
    				for (var i=0; i<=order ; i++){
    					var label = document.getElementById("'.$instance_name.'_'.$property->pmb_name.'_"+i+"_value");
    					var defaultLabel = document.getElementById("'.$instance_name.'_'.$property->pmb_name.'_"+i+"_default_value");
                        if(!label && !defaultLabel) continue;
    					var key = 0;
    					if((label.value != "") || (defaultLabel.value != "")){
    						if(!this.values[key]){
    							this.values[key] = 0;
    						}
    						this.values[key]++;
    					    
    						if(this.nb_values < this.values[key]) {
    							this.nb_values = this.values[key];
    						}
    					}
    				}
                }
				if(this.nb_values < '.$restrictions->get_min().'){
					this.valid = false;
					this.error = "min";
				}
				if('.$restrictions->get_max().' != -1 && this.nb_values > '.$restrictions->get_max().'){
					this.valid = false;
					this.error = "max";
				}
				return this.valid;
			},
			"get_error_message": function(){
 				switch(this.error){
 					case "min" :
						this.message = "'.addslashes($msg['onto_error_no_minima']).'";
						break;
					case "max" :
						this.message = "'.addslashes($msg['onto_error_too_much_values']).'";
						break;
 				}
				this.message = this.message.replace("%s","'.addslashes($property->get_label()).'");
				return this.message;
			}
		}';
	}
	
} // end of onto_common_datatype_ui