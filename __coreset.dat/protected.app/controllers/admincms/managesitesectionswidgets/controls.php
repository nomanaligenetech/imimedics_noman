<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include( APPPATH . 'controllers/admincms/managecmscontent/controls_include.php' );
class Controls extends Controls_Include {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	 
	public function __construct()
	{
		parent::__construct();
		
		$this->_auth_login( false );
		
		$this->data													= $this->default_data();
		
		$this->data["_directory"]									= $this->router->directory;
		$this->data["_pagepath"]									= $this->router->directory . $this->router->class;
		
		
		$this->data["_heading"]										= 'Manage Events / Articles';
		$this->data["_pagetitle"]									= $this->data["_heading"] . " - ";
		$this->data['_pageview']									= $this->data["_directory"] . "view.php";
		
		
		
		#upload files extensions
		$this->data["images_types"]									= "gif|jpg|png";
		$this->data["images_dir"]	 								= "./assets/files/sitesectionwidgets/";
		
		
		
		$this->data["is_dimension"]									= TRUE;
		$this->data["images_width"]									= 839;
		$this->data["images_height"]								= 278;
		
		
		$this->data["is_dimension_2"]								= FALSE;
		$this->data["images_width_2"]								= 830;
		$this->data["images_height_2"]								= 480;
		
		
		
		
		
		#GET INVOLVED MENUS 		
		$obj_parentchild = new ParentChild();
		$obj_parentchild->db_table						=	"tb_cmsmenu";	
		$obj_parentchild->item_identifier_field_name	=	"id";
		$obj_parentchild->parent_identifier_field_name	=	"parentid";
		$obj_parentchild->item_list_field_name			=	"name"; 
		$obj_parentchild->extra_condition				=	" AND (SELECT name FROM tb_cmsmenu_positions WHERE id = tb_cmsmenu.positionid) = '". MENUPOSITION_HEADER ."' AND status = '1' ";
		$obj_parentchild->order_by_phrase				=	" ORDER BY sort ASC ";
		$obj_parentchild->level_identifier				=	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$obj_parentchild->item_pointer					=	"";
		$root_item_id									=	SessionHelper::_get_session("WHATWEDO_MENUID", "site_settings");
		$root_item_id2									=	SessionHelper::_get_session("EVENTS_MENUID", "site_settings");
		
		$extra_select 									= " *, 
															(SELECT name FROM tb_cmsmenu_types WHERE id = tb_cmsmenu.typeid) as type_name,
															(SELECT name FROM tb_cmsmenu_positions WHERE id = tb_cmsmenu.positionid) as position_name  ";
		
		//$this->data['whatwedo_menus'] 					= $obj_parentchild->getAllChilds($root_item_id, '', true, $extra_select);
		$this->data['whatwedo_menus'] 						= $obj_parentchild->getAllChilds($root_item_id2, '', true, $extra_select);
		
		#GET INVOLVED MENUS
	
	
	
		$this->data['content_languages'] = $this->queries->fetch_records("content_languages", " ORDER BY id DESC ")->result_array();
		#pre-filled values for input fields
		$this->_create_fields_for_form(false, $this->data);	
		$this->_create_lang_fields_for_form(false, $this->data, array());
		
		$this->_create_child_for_form(false, $this->data, array() );	
		$this->_create_child_for_form_2(false, $this->data, array() );	
		
		
		$this->_widget_create_child_for_form(false, $this->data, array()); #controls_include
		
	}
	
	
	public function view_table_properties()
	{
		$tmp["tr_heading"]											= array('Title', 'Event', 'Short Desc', 'Image', 'Sort', 'Status', 'Int/Can', 'Last Updated');
		
		return $tmp;
	}
	
	public function view( $is_ajax = 0 )
	{
		$data														= $this->data;
		
		
		$data["table_record"]										= $this->queries->fetch_records("sitesectionswidgets", " ORDER BY id desc ");
		$data["table_properties"]									= $this->view_table_properties();
	

	
		$this->load->view( ADMINCMS_TEMPLATE_VIEW, $data );
		
		
	}
	
	public function add()
	{
		$data												= $this->data;
		
	
		$data['_pageview']									= $data["_directory"] . "edit.php";

		
		$this->load->view( ADMINCMS_TEMPLATE_VIEW, $data );	
	}
	
	
	public function save ()
	{
		$data														= $this->data;
		$languages													= $data["languages"];
		
		if ( ! $this->validations->is_post() )
		{
			redirect ( site_url( $data["_directory"] . "controls/view" ) );
		}
	
		
	
		#re-unite post values + language array with form_validations
		#$this->functions->unite_post_values_form_validation();
		
		
		$this->form_validation->set_rules("id", "id", "trim");
		$this->form_validation->set_rules("options", "options", "trim");
		
		
		$this->form_validation->set_rules("mode", "mode", "trim|required");
		$this->form_validation->set_rules("sitesections_id[]", "Site Sections", "trim|required");
		$this->form_validation->set_rules("year", "Year", "trim");
		if (!empty($this->input->post('don_proj_id'))){
			if(!$this->input->post('id')) {
				$this->form_validation->set_rules("don_proj_id", "Donation Project", "is_unique[tb_sitesectionswidgets.don_proj_id]");
			} else {
				$previousData = $this->queries->fetch_records("sitesectionswidgets", " and id = ".$this->input->post('id'))->result_array();
				if($previousData[0]["don_proj_id"] != $this->input->post('don_proj_id')){
					$this->form_validation->set_rules("don_proj_id", "Donation Project", "is_unique[tb_sitesectionswidgets.don_proj_id]");
				}
			}
		}
		// $this->form_validation->set_rules("title", "Title", "trim|required");
	
	
	
	
	
		$tmp_value		= explode(" - ", $this->input->post("day_and_date") );
		
		$tmp_time_1		= explode(" ", $tmp_value[0]);
		unset($tmp_time_1[0]);
		$tmp_time_1		= implode(" ", $tmp_time_1	);
		
		$tmp_time_2		= explode(" ", $tmp_value[1]);
		unset($tmp_time_2[0]);
		$tmp_time_2		= implode(" ", $tmp_time_2	);
		
		
		//$this->form_validation->set_rules("day_and_date", "Day and Date", "callback_validate_date_time_check[H:i:s,$tmp_time_1,The Day and Date <strong>(Start Time)</strong> is not valid]");
		//$this->form_validation->set_rules("day_and_date", "Day and Date", "callback_validate_date_time_check[H:i:s,$tmp_time_2,The Day and Date <strong>(End Time)</strong> is not valid]");
		
		$this->form_validation->set_rules("address", "Address", "trim");
		
		// $this->form_validation->set_rules("short_desc", "Short Description", "trim|required|max_length[200]");
		// $this->form_validation->set_rules("full_desc", "Full Description", "trim|required");
		$this->form_validation->set_rules("photo_image", "Photo Image", "trim|required");
		$this->form_validation->set_rules("photo_other_image[]","photo_other_image","trim");

		
		if ( 1==1 )
		{
			
			#################################
			###  	upload_image_1  	  ###
			#################################
			$other_upload						= array("validate"											=> TRUE,
														"input_field"										=> "file_photo_image",
														"db_field"											=> "photo_image",
														"input_nick"										=> "Image",
														"hdn_field"											=> "photo_image",
														"tmp_table_field"									=> "upload_1",
														"is_multiple"										=> FALSE,
														
														"validate_dimension"								=> $data['is_dimension'],
														"width"												=> $data['images_width'],
														"height"											=> $data['images_height']);
		
			
			$config_image						= array("upload_path"										=> $data["images_dir"],
														"allowed_types"										=> $data['images_types'],
														"encrypt_name"										=> TRUE);
		
			$config_thumb						= array();
		
			
			$tmp_upload_image_1					= $this->upload_image($config_image, $config_thumb, $other_upload);
		
			
			#insert in tmp table	
			$this->tmp_record_uploads_in_db( TRUE, $tmp_upload_image_1  );
			
			#################################
			###  	upload_image_1  	  ###
			#################################
			
			
			
			
			
			#################################
			###  	photo_other_image	  ###
			#################################
			$other_upload						= array("validate"											=> FALSE,
														"input_field"										=> "file_photo_other_image",
														"db_field"											=> "photo_other_image",
														"input_nick"										=> "Photo Other Image",
														"hdn_field"											=> "photo_other_image",
														"tmp_table_field"									=> "upload_1",
														"is_multiple"										=> TRUE,
		);
														//"validate_dimension"								=> $data['is_dimension_2'],
														//"width"												=> $data['images_width_2'],
														//"height"											=> $data['images_height_2']);
		
			
			$config_image						= array("upload_path"										=> $this->data["images_dir"],
														"allowed_types"										=> $data['images_types'],
														"encrypt_name"										=> TRUE,
														"max_size"											=> '5096');
		
			$config_thumb						= array();
		
			
			$tmp_upload_image_2					= $this->upload_image($config_image, $config_thumb, $other_upload);
			
			#insert in tmp table	
			$this->tmp_record_uploads_in_db( TRUE, $tmp_upload_image_2, TRUE  );
	
			#################################
			###  	product_main_image 	  ###
			#################################
			
		}
		
		
		
		
		$this-> _widget_validate(); #controls_include
		
		$this->rules_for_array_content('title');
		$this->rules_for_array_content('short_desc');
		$this->rules_for_array_content('full_desc');
		
		if( $this->form_validation->run() == FALSE )
		{
			
			$data['_pageview']									= $data["_directory"] . "edit.php";
			$data['_messageBundle']								= $this->_messageBundle( 'danger' , validation_errors(), 'Error!');
			
			$this->load->view( ADMINCMS_TEMPLATE_VIEW, $data );
		}
		else
		{
			
			$short_desc = $this->input->post("short_desc");
			$full_desc = $this->input->post("full_desc");
			$title = $this->input->post("title");
			$user_id =  $this->functions->_admincms_logged_in_details( "id" );
			$saveData											= array("mode"					=> $this->input->post('mode'),
																		"year"					=> $this->input->post('year'),
																		"month"					=> $this->input->post('month'),
																		
																		"title"					=> $title[DEFAULT_LANG_CODE],
																		"slug"					=> $this->queries->make_slug(	"tb_sitesectionswidgets",
																																"id",
																																"slug",
																																"title",
																																$title[DEFAULT_LANG_CODE],
																																$this->input->post("id")
																															),
																		
																		"start_date"			=> format_date( "Y-m-d H:i:s", $tmp_value[0], "d-m-Y H:i:s" ),
																		"end_date"				=> format_date( "Y-m-d H:i:s", $tmp_value[1], "d-m-Y H:i:s" ),
																		
																		"address"				=> $this->input->post('address'),
																		"short_desc"			=> $short_desc[DEFAULT_LANG_CODE],
																		"full_desc"				=> $full_desc[DEFAULT_LANG_CODE],
																		"photo_image"			=> $this->input->post('photo_image'),
																		"status"				=> format_bool( $this->input->post('status') ),
																		"belongsto"				=> $this->input->post('belongsto'),
																		"don_proj_id"			=> $this->input->post('don_proj_id'),
																		"email_text"			=> $this->input->post('email_text'),
																		"date_added"			=> date("Y-m-d H:i:s", strtotime("now")),
																		"added_by"						=> $user_id,
																		"is_package"			=> $this->input->post('is_package')
																	);
			
			
			
			
			if ($this->input->post('options') == "edit")
			{
				$saveData['id']										= $this->input->post('id');
				$this->queries->SaveDeleteTables($saveData, 'e', "tb_sitesectionswidgets", 'id');  

				$this->_save_lang_content($saveData['id'], 'e');
			}
			else
			{
				$this->queries->SaveDeleteTables($saveData, 's', "tb_sitesectionswidgets", 'id');  		
				$saveData['id']										= $this->db->insert_id();

				$this->_save_lang_content($saveData['id'], 's');
			}
			
			
			
			#right widgets
			$this->queries->SaveDeleteTables(array("parentid"			=> $saveData['id']), 'd', "tb_sitesectionswidgets_details", 'parentid');  
			if ( $this->input->post("sitesections_id") )
			{
				foreach ($this->input->post("sitesections_id") as $key => $value )
				{
					$childData					= array("parentid"					=> $saveData['id'],
														"sitesections_id"			=> $value);
					
					
					$this->queries->SaveDeleteTables($childData, 's', "tb_sitesectionswidgets_details", 'id');  		
				}
			}
			
			
			
			#multiple photos
			$this->queries->SaveDeleteTables(array("parentid"			=> $saveData['id']), 'd', "tb_sitesectionswidgets_photos", 'parentid');  
			if ( $this->input->post("photo_other_image") )
			{
				foreach ($this->input->post("photo_other_image") as $key => $value )
				{
					$childData					= array("parentid"					=> $saveData['id'],
														"photo_other_image"			=> $value);
					
					
					
					
					$this->queries->SaveDeleteTables($childData, 's', "tb_sitesectionswidgets_photos", 'id');  		
				}
			}
			
			
			
			
			
			$saveData['id']		= NULL;
			$this->_widget_save( WIDGET_CMSSECTION_ACTIVITIESEVENTS, $saveData ); #controls_include
			
			
			
			$data['_messageBundle']									= $this->_messageBundle( 'success' , 
																							 lang_line("operation_saved_success"), 
																							 lang_line("heading_operation_success"),
																							 false, 
																							 true);

			redirect( $data["_directory"] . "controls/view" );
			
		
		}
		
	}

	private function rules_for_array_content($field = 'content')
	{
		$content = $this->input->post($field);
		if($field){
			$isAllEmpty = true;
			foreach ($content as $key => $record) {
				if(!empty($record)){
					$isAllEmpty = false;
				}
			}
			if($isAllEmpty)
			{
				$this->form_validation->set_rules($field, "Content", "trim|required");
			}
		}
	}

	private function _save_lang_content($ref_id, $action){
		$short_desc = $this->input->post("short_desc");
		$full_desc = $this->input->post("full_desc");
		$title = $this->input->post("title");

		$sitesectionswidgets = $this->queries->fetch_records("sitesectionswidgets_languages", " AND sitesectionswidgets_id = '$ref_id' ")->result_array();

		foreach ($this->data['content_languages'] as $lang_key => $lang) {
			$saveData = [
				'short_desc' => $short_desc[$lang['code']],
				'full_desc' => $full_desc[$lang['code']],
				'title' => $title[$lang['code']],
				'sitesectionswidgets_id' => $ref_id,
				'content_languages_id' => $lang['id']
			];
			
			// if(!$sitesectionswidgets && !in_array($lang['id'], array_column($sitesectionswidgets,'content_languages_id'))){
			if(!$sitesectionswidgets || ($sitesectionswidgets && !in_array($lang['id'], array_column($sitesectionswidgets,'content_languages_id')))){
				$col = 'id';
				$action = 's';
			}else{
				$col = 'content_languages_id|sitesectionswidgets_id';
				$action = 'e';
			}
			
			$this->queries->SaveDeleteTables($saveData, $action, "tb_sitesectionswidgets_languages", $col);
		}
	}
	
	
	
	public function _create_child_for_form( $return_array = false, &$data, $db_data = array() )
	{
	
		$empty_inputs				= array("sitesections_id");
		
		$filled_inputs				= array("sitesections_id");
				
		
		if ($return_array == true)
		{
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				
				#$data[ $empty_inputs[$x] ]					= array();
			}
			
			
			
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				for ($m=0; $m < count($db_data); $m++)
				{
					
					if ( array_key_exists($empty_inputs[$x] , $db_data[$m]) )
					{
					
						$data[ $empty_inputs[$x] ][ $db_data[$m][ $filled_inputs[$x] ] ]			= TRUE;
						//$_POST[ $empty_inputs[$x] ] []		= $db_data[$m][ $filled_inputs[$x] ];
					}
				}
				
				#$this->form_validation->set_rules( $empty_inputs[$x] . "[]", $empty_inputs[$x], "trim");
				
			}
			
			
			
			
			#$this->form_validation->run();
		
		
			return $data;
		}
		else
		{
		
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				if ( $empty_inputs[$x] == "sitesections_id" )
				{
					foreach ($data['whatwedo_menus'] as $value)
					{
						$data[ $empty_inputs[$x] ][$value['id']]		= FALSE;
					}
				}
				
			}
			
			return $data;
		
		}
	}
	
	
	public function _create_child_for_form_2( $return_array = false, &$data, $db_data = array() )
	{
	
		$empty_inputs				= array("photo_other_image");
		
		$filled_inputs				= array("photo_other_image");
				
		
		if ($return_array == true)
		{
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				
				#$data[ $empty_inputs[$x] ]					= array();
			}
			
			
			
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				for ($m=0; $m < count($db_data); $m++)
				{
					
					if ( array_key_exists($empty_inputs[$x] , $db_data[$m]) )
					{
					
						$data[ $empty_inputs[$x] ][]		= $db_data[$m][ $filled_inputs[$x] ];
					}
				}
				
			}
			
			
			return $data;
		}
		else
		{
		
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				$data[ $empty_inputs[$x] ]				= array();
			}
			
			return $data;
		
		}
	}
	
	public function _create_fields_for_form( $return_array = false, &$data, $db_data = array() )
	{
	
		$empty_inputs				= array( "id", "year","month","mode", "start_date", "end_date", "day_and_date|day_time", "address", "photo_image", "status", "belongsto", "is_package", "options", "unique_formid", "don_proj_id", "email_text" );
		
		$filled_inputs				= array( "id", "year", "month","mode", "start_date", "end_date", "start_date", "address", "photo_image", "status", "belongsto", "is_package", "options", "unique_formid", "don_proj_id", "email_text" );
		
		//$filled_inputs				= array( "id", "resorts_id", "promo_code_percentage_discount_code", "promo_code_percentage_discount_percent", "promo_code_percentage_discount_datefrom", "promo_code_percentage_discount_expiry", "promo_code_value_add_code", "promo_code_value_add_details", "promo_code_value_add_datefrom", "promo_code_value_add_expiry",  "options" );
		
		
		if ($return_array == true)
		{
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				
				

				$explode_empty_inputs			= explode( "|", $empty_inputs[$x] );
				$empty_inputs[$x]				= $explode_empty_inputs[0];
				$tmp_value						= $db_data[ $filled_inputs[$x] ];
				
				if ( count($explode_empty_inputs) > 1 )
				{
					switch ( $explode_empty_inputs[1] )
					{
						case "day_time":
							$TMP_sd					= strtotime($db_data[ "start_date" ]);
							if ( $TMP_sd > 0 )
							{
								$tmp_value			= date("d/m/Y h:i A",  $TMP_sd);
								
								
								$TMP_ed					= strtotime($db_data[ "end_date" ]);
								if ( $TMP_ed > 0 )
								{
									$tmp_value		.= " - " . date("d/m/Y h:i A",  $TMP_ed);
								}
							}
							else
							{
								$tmp_value			= "";
							}
							
							break;
							
						case "default_date":
							$d						= strtotime( $db_data[ $filled_inputs[$x] ] );
							if ( $d > 0 )
							{
								$tmp_value			= date("d-m-Y",  $d);
							}
							else
							{
								$tmp_value			= "00-00-0000";
							}
							break;
							
						case "default":	
							break;
					}
				}
				
				$data[ $empty_inputs[$x] ]		= $tmp_value;
				
			}
			
			
			return $data;
		}
		else
		{
		
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				
				$explode_empty_inputs				= explode( "|", $empty_inputs[$x] );
				$empty_inputs[$x]					= $explode_empty_inputs[0];
				$tmp_value							= "";
				
				
				if ( count($explode_empty_inputs) > 1 )
				{
					switch ( $explode_empty_inputs[1] )
					{
						case "default_date":	
							$tmp_value				= "00-00-0000";
							break;
							
						case "default":	
							break;
					}
				}
				
				$data[ $empty_inputs[$x] ]		= $tmp_value;
			}
			
			return $data;
		
		}
	}

	public function _create_lang_fields_for_form( $return_array = false, &$data, $loop_object = array(), $db_data = array() )
	{
	
		$empty_inputs				= array("title", "short_desc", "full_desc");
		$filled_inputs				= array("title", "short_desc", "full_desc");
		
		if ($return_array == true and count($db_data) > 0 )
		{
			
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				foreach ($loop_object as $loop)
				{
					$detail_array[ $loop["id"] ] [ $empty_inputs[$x]]		= "";
				}
			}
			
			
			#loop with the parent object... for e.g. Types of Promo Codes
			foreach ($loop_object as $main)
			{
				
				$language_id				= $main["id"];
				
				for ($x=0;  $x < count($empty_inputs); $x++)
				{
					#second - if value found it will overwrite above array.
					foreach ( $db_data as $loop )
					{
						if ( $loop["content_languages_id"] == $language_id )
						{
							$detail_array[ $main["id"] ] [ $empty_inputs[$x] ]			= $loop[ $filled_inputs[$x] ];	
						}
						
					}
				}
			}			
			
			ksort( $detail_array );
		
			$data["lang_content"]	= $detail_array;
			
			
			return $data;
		}
		else
		{
			
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				foreach ($loop_object as $loop)
				{
					$detail_array[ $loop["id"] ] [ $empty_inputs[$x]]		= "";
				}
			}
			
			$data["lang_content"]	= $detail_array;
			
			return $data;
		
		}
	}
	
	public function _create_array_fields_for_form( $return_array = false, &$data, $loop_object = array(), $db_data = array() )
	{
	
		$empty_inputs				= array( "caption" );
		$filled_inputs				= array( "caption" );
		$languages					= $data["languages"];

		
		
		if ($return_array == true and count($db_data) > 0 )
		{
			
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				foreach ($loop_object as $loop)
				{
					$detail_array[ $loop["id"] ] [ $empty_inputs[$x]]		= "";
				}
			}
			
			
			#loop with the parent object... for e.g. Types of Promo Codes
			foreach ($loop_object as $main)
			{
				
				$language_id				= $main["id"];
				
				for ($x=0;  $x < count($empty_inputs); $x++)
				{
					#second - if value found it will overwrite above array.
					foreach ( $db_data as $loop )
					{
						if ( $loop["languageid"] == $language_id )
						{
							$detail_array[ $main["id"] ] [ $empty_inputs[$x] ]			= $loop[ $filled_inputs[$x] ];	
						}
						
					}
				}
			}			
			
			ksort( $detail_array );
		
			$data["_detail_array"]	= $detail_array;
			
			
			return $data;
		}
		else
		{
			
			

			
			for ($x=0;  $x < count($empty_inputs); $x++)
			{
				foreach ($loop_object as $loop)
				{
					$detail_array[ $loop["id"] ] [ $empty_inputs[$x]]		= "";
				}
			}
			
			$data["_detail_array"]	= $detail_array;
			
			return $data;
		
		}
	}
	
	
	public function edit( $edit_id )
	{
		
		$data														= $this->data;
		$data['_pageview']											= $data["_directory"] . "edit.php";
		
		$data["edit_id"]											= $edit_id;
		$edit_details												= $this->queries->fetch_records("sitesectionswidgets", " AND id = '$edit_id' ");
		//echo $this->db->last_query(); die;
		
		
		if ( $edit_details -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		
		
		
		
		$edit_details												= $edit_details->row_array();
		$edit_details['options']									= "edit";
		$edit_details['unique_formid']								= "";
		
		$this->_create_fields_for_form(true, $data, $edit_details );	

		$edit_lang_details									= $this->queries->fetch_records("sitesectionswidgets_languages", " AND sitesectionswidgets_id = '$edit_id' ")->result_array();
		$this->_create_lang_fields_for_form(true, $data, $this->data['content_languages'], $edit_lang_details );
		
	
		
		$child_details												= $this->queries->fetch_records("sitesectionswidgets_details", " AND parentid = '$edit_id' ");		
		$this->_create_child_for_form(true, $data, $child_details->result_array() );	
		
		$child_details												= $this->queries->fetch_records("sitesectionswidgets_photos", " AND parentid = '$edit_id' ");		
		$this->_create_child_for_form_2(true, $data, $child_details->result_array() );	
		
		
		
		$this-> _widget_edit_child( $data, NULL, WIDGET_CMSSECTION_ACTIVITIESEVENTS ); #controls_include
		
		
		
		$this->load->view( ADMINCMS_TEMPLATE_VIEW, $data );
		
	}
	
	
	public function ajax_update_sorting( $id )
	{
		
		foreach ($id	as $key	=> $result)
		{
			$saveData['sort']										= $result;	
			$saveData['id']											= $key;	
			
			$this->queries->SaveDeleteTables($saveData, 'e', "tb_sitesectionswidgets", 'id') ;
		}
		
		$data['_messageBundle']									= $this->_messageBundle( 'success' , "Sort Updated", 'Success!', FALSE, TRUE);
		
		redirect ( site_url( $this->data["_directory"] . "controls/view" ) );
	}
	
	public function options()
	{
		$data					= $this->data;
		$is_post				= FALSE;
		
		
		
		if ( $_POST['options'] == "ajax_update_sorting" )
		{
			$is_post		= TRUE;
		}
		else if ( isset($_POST['checkbox_options']) )
		{
		
			if (count($_POST['checkbox_options']) > 0 )
			{
				$is_post		= TRUE;
			}
				
		}
		
		
		if ($is_post)
		{
			switch ($_POST['options'])
			{
				
				case "delete":
					$this->delete( $_POST['checkbox_options'] );
					break;
					
				case "ajax_update_sorting":
					$this->ajax_update_sorting( $_POST['sorting'] );
					break;
			
					
				default:
					$data['_messageBundle']								= $this->_messageBundle( 'danger' , "Invalid Operation", 'Error!', true);
					redirect(  site_url( $data["_directory"] . "controls/view" ) );
					break;
				
			}
		}
		else
		{
			$data['_messageBundle']								= $this->_messageBundle( 'danger' , "Invalid Operation", 'Error!', true);
			redirect( site_url( $data["_directory"] . "controls/view" ) );
		}

	}
	
	public function delete( $id )
	{
		$data												= $this->data;
		
		
		
	
		
		#remove images
		foreach ($id	as $key	=> $result)
		{
			$row											= $this->queries->fetch_records("sitesectionswidgets", " AND id = '$result' ")->row();
			$this->remove_file($row->photo_image);
		}
		
		
		
		#remove record from DETAIL table
		foreach ($id	as $key	=> $result)
		{
			$saveData['id']									= $result;	
			$this->queries->SaveDeleteTables($saveData, 'd', "tb_sitesectionswidgets", 'id') ;
			$this->queries->SaveDeleteTables(['sitesectionswidgets_id'=>$result], 'd', "tb_sitesectionswidgets_languages", 'sitesectionswidgets_id') ;
		}
		
		
		
		
		$data['_messageBundle']								= $this->_messageBundle('success' , 
																					lang_line("operation_delete_success"), 
																					lang_line("heading_operation_success"), 
																					false,
																					true);
		redirect(  site_url( $data["_directory"] . "controls/view" )  );
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
