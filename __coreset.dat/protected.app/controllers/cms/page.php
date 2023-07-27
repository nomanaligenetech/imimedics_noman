<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page extends C_frontend {

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
		
		
		$this->data													= $this->default_data();
		
		$this->data['_messageBundle2']								= $this->_messageBundle( 'warning' , 'To be announced soon' );
		$this->data['_pageview']									= "frontend/cms/page.php";
		
		$this->data['_show_title']									= FALSE;
		$this->data['_show_icon_with_title']						= FALSE;
		
	}
	
	function recursive_menus( $menu_id = FALSE, $is_current_menu, &$return_array = array(), $sort_asc = TRUE, $is_exceptional = FALSE )
	{
		
		$TMP_attributes									= array();
		
		if (  $is_current_menu  )
		{
			#current_item
			$TMP											= $this->queries->fetch_records("cmsmenu", " AND id = '". $menu_id ."' ");
			$TMP_content									= $this->mixed_queries->fetch_records("cmscontent", " AND  menuid = '". $menu_id ."' ");	
			$TMP_attributes									= $this->functions->set_link_attributes( $TMP->row_array(), $TMP_content, SLUG_PAGE );
			$TMP_attributes['link']							= $TMP_attributes['href'];
			$TMP_attributes['is_active']					= TRUE;
			$return_array[ $TMP->row('id') ]				= $TMP_attributes; #array("name"				=> $TMP->row('name') );	
		}
		
		
		
		
		#recursive -> parent - child - subchild
		$this->queries->fetch_records("cmsmenu", " AND id = '". $menu_id ."' ", "parentid");
		$TMP												= $this->queries->fetch_records("cmsmenu", " AND id IN (". $this->db->last_query() .") $is_exceptional ");
		foreach ( $TMP->result_array() as $t)
		{
			$TMP_attributes									= array();
			$TMP_content									= $this->mixed_queries->fetch_records("cmscontent", " AND  menuid = '". $t['id'] ."' ");	
			$TMP_attributes									= $this->functions->set_link_attributes( $t, $TMP_content, SLUG_PAGE );
			$TMP_attributes['link']							= $TMP_attributes['href'];
			$return_array[ $t['id'] ]						= $TMP_attributes;
			
			#sorting values:
			if ( $sort_asc )
			{
				ksort( $return_array );	
			}
			
			
			
			$this->recursive_menus( $t['id'], FALSE, $return_array, $sort_asc, $is_exceptional );
		}
	}
	
	
	
	public function breadcrumbs( $menu_parent_id = FALSE )
	{		
			//echo $menu_parent_id; die;

		$TMP_array[]												= array("name"			=> lang_line('text_home'),
																			"link"			=> site_url());
		
		
		
		$TMP_breadcrumbs											= array();
		$TMP_is_exceptional											=  $this->queries->fetch_records("cmsmenu", 
																									 " 	AND id = '". $menu_parent_id ."' 
																									 	AND (SELECT name FROM tb_cmsmenu_positions WHERE id = tb_cmsmenu.positionid) IN ('footer') " );
		

		if ( $TMP_is_exceptional -> num_rows() > 0 )
		{
			$TMP_is_exceptional										= " AND parentid != '0' ";	
		}
		else
		{
			$TMP_is_exceptional										= FALSE;
		}
		
		$this->recursive_menus( $menu_parent_id, TRUE, $TMP_breadcrumbs, TRUE, $TMP_is_exceptional ); 
		
		foreach ( $TMP_breadcrumbs as $b_key => $b_value)
		{
			$cmsmenu_languages = $this->queries->fetch_records("cmsmenu_languages", " AND cmsmenu_id = {$b_key}")->result_array();
			replace_data_for_lang($b_value, $this->data['content_languages'], $cmsmenu_languages, ['name'], SessionHelper::_get_session('LANG_CODE') );

			$TMP_array[]											= $b_value;
		}
		
		
		
	//echo print_r($TMP_array); die;
		return $TMP_array;
	}
	
	public function breadcrumbs_chapters( $menu_parent_id = FALSE, $breadcrumb_details,  $current_page_title = FALSE)
	{
		$TMP_array			= array();
		if ( $breadcrumb_details -> num_rows() > 0 )
		{
			foreach ($breadcrumb_details -> result_array() as $bd )
			{
				if ( $this->validations->is_numeric( $bd['breadcrumb_menuid'] ) > 0 )
				{
					
					$TMP_cmsmenu									= $this->queries->fetch_records("cmsmenu", " 	AND id = '". $bd['breadcrumb_menuid'] ."' " );
					$TMP_content									= $this->mixed_queries->fetch_records("cmscontent", " AND  menuid = '". $bd['breadcrumb_menuid'] ."' ");	
					$TMP_attributes									= $this->functions->set_link_attributes( $TMP_cmsmenu->row_array(), $TMP_content, SLUG_PAGE );
					$TMP_attributes['link']							= $TMP_attributes['href'];
									
					$TMP_array[]									= $TMP_attributes;			
			
				}	
				else
				{
					$TMP_array[]												= array(//"name"			=> $bd['breadcrumb_text'],
																						"name"			=> lang_line('text_home'),
																						"link"			=> $this->functions->content_shortcodes( $bd['breadcrumb_link'] ) );
					
					
				}
			}
		}		
	
		$TMP_array[]												= array("name"			=> $current_page_title,
																			"is_active"		=> TRUE);
		return $TMP_array;
	}
	
	public function breadcrumbs_timeline( $menu_parent_id = FALSE, $breadcrumb_details,  $current_page_title = FALSE)
	{
		$TMP_array			= array();
		if ( $breadcrumb_details -> num_rows() > 0 )
		{
			foreach ($breadcrumb_details -> result_array() as $bd )
			{
				if ( $this->validations->is_numeric( $bd['breadcrumb_menuid'] ) > 0 )
				{
					
					$TMP_cmsmenu									= $this->queries->fetch_records("cmsmenu", " 	AND id = '". $bd['breadcrumb_menuid'] ."' " );
					$TMP_content									= $this->mixed_queries->fetch_records("cmscontent", " AND  menuid = '". $bd['breadcrumb_menuid'] ."' ");	
					$TMP_attributes									= $this->functions->set_link_attributes( $TMP_cmsmenu->row_array(), $TMP_content, SLUG_PAGE );
					$TMP_attributes['link']							= $TMP_attributes['href'];
									
					$TMP_array[]									= $TMP_attributes;			
			
				}	
				else
				{
					$TMP_array[]												= array("name"			=> $bd['breadcrumb_text'],
																						"link"			=> $this->functions->content_shortcodes( $bd['breadcrumb_link'] ) );
					
					
				}
			}
		}		
	
		$TMP_array[]												= array("name"			=> $current_page_title,
																			"is_active"		=> TRUE);
		return $TMP_array;
	}
	
	public function breadcrumbs_events_activities( $menu_parent_id = FALSE, $details, $mode)
	{		

	
		$TMP_array[]												= array("name"			=> lang_line('text_home'),
																			"link"			=> 
																			site_url());
		
		
		$mode														= DropdownHelper::eventactivities_dropdown( $details->row("mode") );
		$TMP_breadcrumbs											= array();
		
		#dont include current item (collect recursive items for menus)
		$this->recursive_menus( $menu_parent_id, TRUE, $TMP_breadcrumbs, TRUE, FALSE ); 
		foreach ( $TMP_breadcrumbs as $b_key => $b_value)
		{
			$cmsmenu_languages = $this->queries->fetch_records("cmsmenu_languages", " AND cmsmenu_id = {$b_key}")->result_array();
			replace_data_for_lang($b_value, $this->data['content_languages'], $cmsmenu_languages, ['name'], SessionHelper::_get_session('LANG_CODE') );
			unset($b_value['is_active']);
			$TMP_array[]											= $b_value;
		}
		
		
		
		
		
		$TMP_menu_detail											= $this->queries->fetch_records('cmsmenu', " AND id = '". $menu_parent_id ."' AND status = '1'");
		#add only Text e.g. Activities
		$TMP_array[]												= array(
																			"name"		=> $mode,
																			"link"		=> site_url( "page/" . $TMP_menu_detail->row("slug") . "/" . $details->row("mode")  ) 
																			);
		
		
		
		
		#add current item 
		if ( $details -> num_rows() > 0 )
		{
			$TMP_array[]											= array("name"				=> $details -> row("title"),
																			"is_active"			=> TRUE);
		}
		
	//echo "<pre>";
	//print_r($TMP_array); die;
		
		return $TMP_array;
	}
	
	
	public function breadcrumbs_events_or_activities_list( $menu_parent_id = FALSE, $mode)
	{		
	

		$TMP_array[]												= array("name"			=> lang_line('text_home'),
																			"link"			=> site_url());
		
		
		$mode														= DropdownHelper::eventactivities_dropdown( $mode );
		$TMP_breadcrumbs											= array();
		
		#dont include current item (collect recursive items for menus)
		$this->recursive_menus( $menu_parent_id, TRUE, $TMP_breadcrumbs, TRUE, FALSE ); 
		foreach ( $TMP_breadcrumbs as $b_key => $b_value)
		{
			unset($b_value['is_active']);
			$TMP_array[]											= $b_value;
		}
		
		
		
		
		
		$TMP_menu_detail											= $this->queries->fetch_records('cmsmenu', " AND id = '". $menu_parent_id ."' AND status = '1'");
		#add only Text e.g. Activities
		$TMP_array[]												= array(
																			"name"				=> $mode,
																			"link"				=> site_url( "page/" . $TMP_menu_detail->row("slug") . "/" . $mode  ) ,
																			"is_active"			=> TRUE
																			);
		
		
		
		
		
	
		
		return $TMP_array;
	}
	
	
	
	public function index( $slug = '', $slug2 = FALSE )
	{
   

		$siteIdQuery							= getSiteId();
     	$data									= $this->data;
		
		$data['menu_detail']					= $this->queries->fetch_records('cmsmenu', " AND slug = '". $slug ."' AND status = '1'"); 
		$data['menu_detail']				= new CustomMySql($data['menu_detail'], $this, 'cmsmenu', ['name','subheading']);
		//$data['menu_detail']					= $this->queries->fetch_records('cmsmenu', " AND slug = '". $slug ."' AND status = '1'".$siteIdQuery);
		if ( $data['menu_detail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		
		$data['menu_active']					= $data['menu_detail']->row("name");
		$data['_pagetitle']						= $data['menu_detail']->row("name");
		$data['_is_breadcrumbs']				= $this->breadcrumbs( $data['menu_detail']->row("id") );
		
	//	var_dump($data['_is_breadcrumbs']); die;
		
		//$data['content_detail']					= $this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		$data['content_detail']					= new CustomMySql($this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ".$siteIdQuery), $this);
		
		// $cmscontent_languages = $this->queries->fetch_records("cmscontent_languages", " AND cmscontent_id = {$data['content_detail']->row('id')}")->result_array();
		// replace_data_for_lang($data['content_detail']->result_array(), $this->data['content_languages'], $cmscontent_languages, ['short_desc','content'], SessionHelper::_get_session('LANG_CODE') );
		
		
		
		// echo "<pre>";
		// print_r ($data['content_detail']);
		// echo "</pre>";
		// exit;
		
		if ( $data['menu_detail']->row("show_title") )
		{
			$data['_show_title']					= $data['menu_detail']->row("name");	
		}
		
		if ( $data['menu_detail']->row("show_icon_with_title") )
		{
			$data['_show_icon_with_title']			= $data['menu_detail']->row("photo_image_icon");
		}
		
		$this->_call( $data, WIDGET_CMSSECTION_CMSCONTENT, $data['content_detail']->row("id") );
		
	}
	
	
	public function chapters( $slug = '' )
	{
		
		$data									= $this->data;
		
		$data['menu_detail']					= $this->queries->fetch_records('chapterslocation_master', " AND slug = '". $slug ."' AND status = '1'");
		$data['menu_detail']					= new CustomMySql($data['menu_detail'], $this, 'chapterslocation_master', ['title', 'short_desc', 'full_desc']);
		$data['menu_detail']->row()->content = $data['menu_detail']->row()->full_desc;
		
		if ( $data['menu_detail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		$data['slug']							= $slug;
		$data['menu_active']					= $data['menu_detail']->row("title");
		$data['_pagetitle']						= $data['menu_detail']->row("title");
		
		$data['breadcrumbs_details']			= $this->queries->fetch_records('chapterslocation_breadcrumbs', " AND parentid = '". $data['menu_detail']->row("id") ."' ");
		$data['_is_breadcrumbs']				= $this->breadcrumbs_chapters( $data['menu_detail']->row("id"), $data['breadcrumbs_details'], $data['menu_detail']->row("title") );
		
		
			
		
		
		
		$data['_show_title']					= $data['menu_detail']->row("title");
		if ( $data['menu_detail']->row("show_map_with_title") )
		{
			$data['_show_icon_with_title']		= ('http://www.geonames.org/flags/x/'. strtolower( $data['menu_detail']->row("countries_iso_code_2") ) .'.gif');
		}
		
		
		$data['content_detail']					= $data['menu_detail']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		
		
		$this->_call( $data, WIDGET_CMSSECTION_CHAPTERLOCATION, $data['menu_detail']->row("id"), 'chapterlocation_widgets' );
	}
	
	public function timeline( $slug = '' )
	{
		
		$data									= $this->data;
		
		$menu_detail							= $this->queries->fetch_records('timelinehistory', " AND slug = '". $slug ."' AND status = '1'");
		$data['menu_detail']					= new CustomMySql($menu_detail, $this, 'timelinehistory', ['title','short_desc','full_desc']);
		$data['menu_detail']->row()->content = $data['menu_detail']->row()->full_desc;
		
		if ( $data['menu_detail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		$data['slug']							= $slug;
		$data['menu_active']					= $data['menu_detail']->row("title");
		$data['_pagetitle']						= $data['menu_detail']->row("title");
		
		
		#$data['_is_breadcrumbs']				= $this->breadcrumbs_chapters( $data['menu_detail']->row("id"), $data['breadcrumbs_details'], $data['menu_detail']->row("title") );
		
		
			
		
		
		if ( $data['menu_detail']->row("show_title") )
		{
			$data['_show_title']				= $data['menu_detail']->row("title");
		}
		
		
		
		
		$data['content_detail']					= $data['menu_detail']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		
		
		$this->_call( $data, WIDGET_CMSSECTION_TIMELINE );
	}
	

	public function events_activities( $page_slug = FALSE, $events_activities_slug = '' )
	{
	
		
		$data									= $this->data;
		
		
		$data['menu_detail']					= $this->queries->fetch_records('cmsmenu', " AND slug = '". $page_slug ."' AND status = '1'");
		if ( $data['menu_detail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		
		
		// $data['sitesectionswidgets']			= $this->queries->fetch_records('sitesectionswidgets', " AND slug = '". $events_activities_slug ."' AND status = '1'");
		$data['sitesectionswidgets']			= new CustomMySql($this->queries->fetch_records('sitesectionswidgets',  " AND slug = '". $events_activities_slug ."' AND status = '1'"), $this, 'sitesectionswidgets', ['title','short_desc','full_desc']);
		$data['sitesectionswidgets']->row()->content = $data['sitesectionswidgets']->row()->full_desc;

		if ( $data['sitesectionswidgets'] -> num_rows() <= 0 )
		{
			show_404();
		}
		if ($this->uri->segments[5]) {
			require(APPPATH . 'controllers/cms/plugins/pp_events_activities_this_menu.php');
			switch ($this->uri->segments[5]) {
				case 'paymentsuccess':
					PP_Events_Activities_This_Menu::payment_success($data, $this, $data['sitesectionswidgets']->row('email_text'));
					break;
				case 'paymentnotify':
					PP_Events_Activities_This_Menu::payment_success($data, $this, $data['sitesectionswidgets']->row('email_text'));
					break;
				case 'paymentcancel':
					PP_Events_Activities_This_Menu::payment_cancel($data, $this, $data['sitesectionswidgets']->row('email_text'));
					break;
			}
		} else {
		
	
			$data['sitesectionswidgets_details']	= $this->queries->fetch_records('sitesectionswidgets_details', 
																					" 	AND sitesections_id = '". $data['menu_detail']->row("id") ."' 
																						AND parentid = '". $data['sitesectionswidgets']->row("id") ."' ");
			if ( $data['sitesectionswidgets_details'] -> num_rows() <= 0 )
			{
				show_404();
			}
			
			
			
			

		
			
			switch ( $data['sitesectionswidgets']->row("mode") )
			{
				case "events":
					$TMP_default_size_images				= 7;
					$data['IMAGES_details']['size']			= array("width"			=> 1000,
																	"height"		=> 1000);
					
					
					$data['IMAGES_details']['thumb_size']	= array("width"			=> 120,
																	"height"		=> 90);
					
					$data['is_events']						= TRUE;
					break;
				
				default:
					$TMP_default_size_images				= 1;
					$data['IMAGES_details']['size']			= array("width"			=> 826,
																	"height"		=> 505	);
					
					
						
					$data['IMAGES_details']['thumb_size']	= array("width"			=> 120,
																	"height"		=> 90);
					break;
			}
			
			
			
			$TMP_sliderimages						= $this->queries->fetch_records('sitesectionswidgets_photos', 
			" AND parentid = '". $data['sitesectionswidgets']->row("id") ."' ");
			
			$data['slider_images']					= $TMP_sliderimages->result_array();
				
			
			//$this->functions->nested_three_images_slider( $TMP_sliderimages->result_array(), "photo_other_image", $data['slider_images'], $TMP_default_size_images );
			
		
			
			
			
			
			#$data['slug']							= $events_activities_slug;
			$data['menu_active']					= $data['sitesectionswidgets']->row("title");
			$data['_pagetitle']						= $data['sitesectionswidgets']->row("title");
			
			
			$data['_is_breadcrumbs']				= $this->breadcrumbs_events_activities( $data['menu_detail']->row("id"), $data['sitesectionswidgets'], "Activities" );
			
			
				
			
			
			
			$data['_show_title']					= $data['sitesectionswidgets']->row("title");
			if ( $data['menu_detail']->row("show_map_with_title") )
			{
				$data['_show_icon_with_title']		= $data['menu_detail']->row("photo_image_icon");
			}
			
			
			$data['content_detail']					= $data['sitesectionswidgets']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
			
			$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
		}
	}
	
	public function events_or_activities_list( $page_slug = FALSE, $mode = FALSE)
	{
	
		$data									= $this->data;
		
		
		$data['menu_detail']					= $this->queries->fetch_records('cmsmenu', " AND slug = '". $page_slug ."' AND status = '1'");
		if ( $data['menu_detail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		

		
		#$data['slug']							= $events_activities_slug;
		$data['menu_active']					= $data['menu_detail']->row("name");
		$data['_pagetitle']						= $data['menu_detail']->row("name");
		$data['sitesections_mode']				= $mode;
		
		
		$data['_is_breadcrumbs']				= $this->breadcrumbs_events_or_activities_list( $data['menu_detail']->row("id"), $mode );
		
		
			
		
		
		$TMP_mode								= DropdownHelper::eventactivities_dropdown( $mode );
		if ( $data['menu_detail']->row("show_title") )
		{
			$data['_show_title']					= $TMP_mode;
		}
		
		if ( $data['menu_detail']->row("show_icon_with_title") )
		{
			$data['_show_icon_with_title']			= $data['menu_detail']->row("photo_image_icon");
		}
		
		
		$data['content_detail']					= $this->db->query(' SELECT "['. strtoupper( $TMP_mode ) .'_THIS_MENU]" as "content" '); 
		#$data['sitesectionswidgets']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		
		$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
	}
	

	
	public function _call( $data, $widget_cmssection = NULL, $widget_parent_id = NULL, $widgets_table = 'cmscontent_widgets' )
	{


		
		if ( $data['content_detail']->num_rows() > 0 )
		{
		//die($widget_cmssection); 
			
			
			$data['widget_parent_id'] = $widget_parent_id;
			$TMP_parentid_edit											= " AND parentid = '$widget_parent_id' ";
			if ( $widget_parent_id == NULL )
			{
				$TMP_parentid_edit										= " AND parentid is NULL ";
			}
		
		
			#get widgets
			$data['left_widgets']				= $this->queries->fetch_records($widgets_table, 
																				" 	$TMP_parentid_edit AND is_mode = 'left' AND cmssection = '$widget_cmssection' ")->result_array();
			
			
			$data['right_widgets']				= $this->queries->fetch_records($widgets_table, 
																				" 	$TMP_parentid_edit AND is_mode = 'right' AND cmssection = '$widget_cmssection' ")->result_array();
			
			
			$data['center_widgets']				= $this->queries->fetch_records($widgets_table, 
																				" 	$TMP_parentid_edit AND is_mode = 'center' AND cmssection = '$widget_cmssection' ")->result_array();
			
		}
		
		//echo "<pre>"; print_r($data['left_widgets']); die;
		
		#plugins..
		include_once(APPPATH.'controllers/cms/plugins/plugins.php');
		$Plugins								= new Plugins( $data );
		
		$Plugins->checkout	( $data );
		$Plugins->render	( $data );
		
		
		if ( isset($this->session->flashdata('_flash_post_data')['_pageview']) )
		{
			/*die("1");*/
			$data['_pageview']									= $this->session->flashdata('_flash_post_data')['_pageview'];
		}
		
		
		if ( $data['right_widgets'] and $data['center_widgets'] )
		{
		/*	echo FRONTEND_TEMPLATE_LEFT_RIGHT_CENTER_WIDGETS_VIEW;
			die("2");*/
			$this->load->view( FRONTEND_TEMPLATE_LEFT_RIGHT_CENTER_WIDGETS_VIEW, $data );	
		}
		else if ( $data['right_widgets'] and $data['left_widgets'] )
		{
			/*echo "<pre>";
			print_r($data);
			echo FRONTEND_TEMPLATE_LEFT_RIGHT_WIDGETS_VIEW; die;
		*/	//die("3");
			$this->load->view( FRONTEND_TEMPLATE_LEFT_RIGHT_WIDGETS_VIEW, $data );	
		}
		else if ( $data['left_widgets'] )
		{
			/*die("4");*/
			//echo "<pre>";
			//print_r($data);	
			//echo  FRONTEND_TEMPLATE_LEFT_WIDGETS_VIEW; die;
			$this->load->view( FRONTEND_TEMPLATE_LEFT_WIDGETS_VIEW, $data );

			// $_load_view = isset($_POST['is_event_success']) ? FALSE : TRUE;
			// if( $_load_view ){
			// $this->load->view( FRONTEND_TEMPLATE_LEFT_WIDGETS_VIEW, $data );
			// } else{
			// 	unset($_POST['is_event_success']);
			// }			
		}
		else if ( $data['right_widgets'] )
		{
			/*die("5");*/
			$this->load->view( FRONTEND_TEMPLATE_RIGHT_WIDGETS_VIEW, $data );	
		}
		else
		{
			$this->load->view( FRONTEND_TEMPLATE_CENTER_WIDGETS_VIEW, $data );	
		}	
	}
	
	public function press_releases_details( $press_slug = FALSE, $press_details_slug = FALSE )
	{
		$data									= $this->data;		
		
		$data['is_pressrelease']				= TRUE;
		$data['pressreleasedetail']			= $this->queries->fetch_records('pressRelease', " AND slug = '". $press_details_slug ."' ORDER BY id DESC");
		if ( $data['pressreleasedetail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		/*
		$data['menu_detail']					= $this->queries->fetch_records('cmsmenu', " AND slug = 'events' AND status = '1'");
		if ( $data['menu_detail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		*/
		
		$data['content_detail']					= $data['pressreleasedetail']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		
		
		$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
	}
	
	public function imi_news_detail( $imi_slug = FALSE, $imi_details_slug = FALSE )
	{


		$data								= $this->data;		
		
		$data['is_imi_news']				= TRUE;
		$data['iminewsdetail']				= $this->queries->fetch_records('news', " AND slug = '". $imi_details_slug ."' ORDER BY id DESC");
		
		if ( $data['iminewsdetail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
		
		$data['content_detail']					= $data['iminewsdetail']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		
		
		$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
	}

	public function imi_topic_detail($imi_slug = '', $is_id='', $column='date', $sortby='desc',$page='')
	{
  
    $userid=$this->functions->_user_logged_in_details('id'); 

    if($userid==0){

			redirect ( site_url("memberlogin") );
     }

        $this->restrictToApprovedPaidImiMembers($userid);

   //	$column='date';$sortby='desc';
 
        $data                                              =$this->data;

        $config = array();
 		
 		//echo $page; die;
	   // $page = ($this->uri->segment(8)) ? $this->uri->segment(8) : 0;
	  
        if($page==''){
        	$page=0;
        }

	    $config["base_url"] = site_url() ."page/discussion-board/topic_detail/".$is_id."/".$column."/".$sortby;
      
        $numbers = $this->queries->fetch_records("getPosts","","(SELECT COUNT(*) FROM tb_posts WHERE topic_id={$is_id}) as count")->result()[0];
	
		$config["total_rows"]=$numbers->count;      
        $config["per_page"] = 4;
        $config["uri_segment"] = 7;
        $config['full_tag_open'] = "<ul>";
		$config['full_tag_close'] ="</ul>";
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = "<li class='active'>";
		$config['cur_tag_close'] = "</a></li>";
		$config['next_tag_open'] = "<li>";
		$config['next_tagl_close'] = "</li>";
		$config['prev_tag_open'] = "<li>";
		$config['prev_tagl_close'] = "</li>";
		$config['first_tag_open'] = "<li>";
		$config['first_tagl_close'] = "</li>";
		$config['last_tag_open'] = "<li>";
		$config['last_tagl_close'] = "</li>";
     
        $this->pagination->initialize($config);
        
        $perpage=$config['per_page'];
       
    
        $data['is_topic_detail']				= TRUE;
    
        $data["postdetail"] = $this->queries->fetch_records("getPosts","AND topic_id=$is_id ORDER BY $column $sortby LIMIT $page,$perpage");
      	
        $data["links"]= $this->pagination->create_links();
        

		$edit_details 												= $this->queries->fetch_records("getTopics", " ORDER BY `id` DESC");
		$edit_details												= $edit_details->row_array();
		$edit_details['options']									= "edit";
		$edit_details['unique_formid']								= "";
		$data['_topic_id']											=$is_id;


		$topcname                                                    =$this->queries->fetch_records("getforumtopics","AND id='".$is_id."'")->result()[0]; 
		$data['_topicname']                                          =$topcname->name;          
		$data['_forumid']											 =$topcname->frmid;
		$data['_slug']											     =$topcname->slug;
		$data['_topicid']											 =$topcname->id;
	    
	    //$data['menu_slug']	=>    $data['menu_detail']->row("slug"),

	//	$forumt = $this->queries->fetch_records("getforumtopics","AND id=".$is_id."");

		$data['content_detail']					= $this->queries->fetch_records("getforumtopics","AND id={$is_id}"); #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		//var_dump($data['content_detail']); die;
		
		$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );

	
	}
	public function comments($imi_slug='',$is_id=''){

	$userid=$this->functions->_user_logged_in_details('id'); 

    if($userid==0){

			redirect ( site_url("memberlogin") );
     }

        $this->restrictToApprovedPaidImiMembers($userid);

	  $data														= $this->data;
	
		$views = $this->queries->fetch_records("getPosts","AND id=".$is_id,"views")->result()[0];
		$updateViews = array('views'=>$views->views + 1,'id'=>$is_id);

		$this->queries->SaveDeleteTables($updateViews, 'e', "tb_posts","id"); 



		 $userid=$this->functions->_user_logged_in_details('id'); 
		
     	$data['is_comment']				= TRUE;
	
	    $data['_post_id']											=$is_id;


		$post_name                                                   =$this->queries->fetch_records("getPosts","AND id='".$is_id."'")->result()[0]; 
		$data['_postname']                                           =$post_name->name;          
		
		$data['_topic_id']											 =$post_name->topic_id;
		$topic 												         = $this->queries->fetch_records("getforumtopics", " AND id ='".$post_name->topic_id."' ORDER BY `id` DESC")->result()[0];
		$data['_topicname']											 =$topic->name;
  		$data['_forumid']											 =$topic->frmid;
  		$forum 												         = $this->queries->fetch_records("forums", " AND id ='".$topic->frmid."' ORDER BY `id` DESC")->result()[0];
		$data['_forumname']											 =$forum->name;
  		
		$postdetail = $this->queries->fetch_records("getPosts","AND id=".$is_id."");

//var_dump($postdetail);die;

		$data['postdetail']			=$postdetail->result()[0];

     	$data['commentdetail']		=$this->queries->fetch_records("getComments",'AND post_id="'.$is_id.'" AND parentid = 0');
	    $data['content_detail']		= $postdetail;
	   

	    $this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
	}


public function add_topic(){

	$userid=$this->functions->_user_logged_in_details('id'); 
    if($userid==0){
			redirect ( site_url("memberlogin") );
     }

    $this->restrictToApprovedPaidImiMembers($userid);


      if($this->input->post("edit")=="edit"){

       	$data									= $this->data;

		#standard validation
		$this->functions->unite_post_values_form_validation();

        #To get user id 
        $userid=$this->functions->_user_logged_in_details('id'); 

		$this->form_validation->set_rules('name', 'Post Name', 'trim|required');
		
		if( $this->form_validation->run() == FALSE )
		{
			//$data['_pageview']										  = $this->TMP_dir . $data["_directory"] . "view.php";
			$data['_messageBundle']									  = $this->_messageBundle( 'danger' , validation_errors(), 'Error!');
			$data['forums_details']									= $this->queries->fetch_records("forumsdetails", " ORDER BY id DESC ");	

			redirect ( site_url("page/discussion-board",$data) );
	   

	 	}
		else
		{

			$saveData					= array(
												"id"					=> $this->input->post("id"),
											    "name"					=> $this->input->post("name"),
											    "description"      		=> $this->input->post("description"),
												"slug"		       		=> $this->queries->make_slug("tb_forum_topics",
																									   "id",
																									   "slug",
																									   "name",
																									   $this->input->post("name"),
																									 $this->input->post("id")),
												
												);

			
			$topic_id = $this->input->post("id");
			$old_topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");
			$old_topic = $old_topic_details->row();
			
			$this->queries->SaveDeleteTables($saveData, 'e', "tb_forum_topics","id"); 

			$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
			$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");
			
			$user = $user_details->row();
			$topic = $topic_details->row();
			
			$forum_details = $this->queries->fetch_records('forums', " AND id='" . $topic->frmid . "'");
			$forum = $forum_details->row();
			
			/* Mail to Admin */

			$heading = 'A user "' . $user->name . '" has updated from topic "'.$old_topic->name.'" to "'. $topic->name .'" in forum "' . $forum->name . '"';
			
			$email_template = array(
				"email_heading" => $heading,
				"email_file" => "email/frontend/topic_email.php",
				"email_subject" => $heading,
				"default_subject" => true,
				"email_post" => array('user' => $user, 'forum' => $forum, 'topic' => $topic,'old_topic'=>$old_topic,'updated'=>true)
			);

			$is_email_sent = $this->_send_email($email_template);
			/* Mail to Admin */
			
		//	$data['_pageview']										  = $this->TMP_dir . $data["_directory"] . "view.php";
			$data['_messageBundle']									    = $this->_messageBundle( 'success' , 
																							 "Your topic updated successfully.", 
																				 lang_line("heading_operation_success"),
																								 true);
			

		$data['forums_details']									= $this->queries->fetch_records("forumsdetails", " ORDER BY id DESC ");	
		redirect ( site_url("page/discussion-board",$data) );
	   
  
	   	}		
	  

   	}else{

       	$data									= $this->data;

		#standard validation
		$this->functions->unite_post_values_form_validation();

        #To get user id 
        $userid=$this->functions->_user_logged_in_details('id'); 

		$this->form_validation->set_rules('name', 'Post Name', 'trim|required');
		
		if( $this->form_validation->run() == FALSE )
		{
			//$data['_pageview']										  = $this->TMP_dir . $data["_directory"] . "view.php";
			$data['_messageBundle']									  = $this->_messageBundle( 'danger' , validation_errors(), 'Error!');
			$data['forums_details']									= $this->queries->fetch_records("forumsdetails", " ORDER BY id DESC ");	
		
		redirect ( site_url("page/discussion-board",$data) );
	   


	 	}
		else
		{

			$saveData					= array(
											   	"name"					=> $this->input->post("name"),
											    "description"      		=> $this->input->post("description"),
												"frmid"			   		=> $this->input->post("forumid"),
												"slug"		       		=> $this->queries->make_slug("tb_forum_topics",
																									   "id",
																									   "slug",
																									   "name",
																									   $this->input->post("name"),
																									 $this->input->post("id")),
												"created_by_user"       => $userid
												);
			
			$this->queries->SaveDeleteTables($saveData, 's', "tb_forum_topics","id");
			$topic_id	= $this->db->insert_id();

			$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");
			
			$topic = $topic_details->row();

			$forum_id = $this->input->post("forumid");

			$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
			$forum_details = $this->queries->fetch_records('forums', " AND id='" . $forum_id . "'");

			$user = $user_details->row();
			$forum = $forum_details->row();
			
			/* Mail to Admin */

			$heading = 'A user "' . $user->name . '" has added new topic "'. $topic->name .'" in forum "' . $forum->name . '"';
			
			$email_template = array(
				"email_heading" => $heading,
				"email_file" => "email/frontend/topic_email.php",
				"email_subject" => $heading,
				"default_subject" => true,
				"email_post" => array('user' => $user, 'forum' => $forum, 'topic' => $topic)
			);

			$is_email_sent = $this->_send_email($email_template);
			/* Mail to Admin */


		//	$data['_pageview']										  = $this->TMP_dir . $data["_directory"] . "view.php";
			$data['_messageBundle']									    = $this->_messageBundle( 'success' , 
																							 "Your topic submited successfully.", 
																				 lang_line("heading_operation_success"),
																								 true);
			

		$data['forums_details']									= $this->queries->fetch_records("forumsdetails", " ORDER BY id DESC ");	
		
		redirect ( site_url("page/discussion-board",$data) );
	   
	   	}		
	  
	  }
    
   }

   public function add_post(){


    $userid=$this->functions->_user_logged_in_details('id'); 

    if($userid==0){

			redirect ( site_url("memberlogin") );
     }

       $this->restrictToApprovedPaidImiMembers($userid);

      if($this->input->post("edit")=="edit"){
      	$data														= $this->data;
		
		#standard validation
		$this->functions->unite_post_values_form_validation();
		
        #To get user id 
        $userid=$this->functions->_user_logged_in_details('id'); 
		
		$this->form_validation->set_rules('name', 'Post', 'trim|required');
		$this->form_validation->set_rules('topic_id', 'Topic Id', 'trim|required');
		
		if( $this->form_validation->run() == FALSE )
		{
		    $data['_topic_id']									=$is_id;
		//	$data['_pageview']									= $this->TMP_dir . $data["_directory"] . "view.php";
			$data['_messageBundle']								=$this->_messageBundle( 'danger' , 
																							 "Fields are empty .", 
																							 lang_line("heading_operation_danger"),
																							 false, 
																							 true);
			redirect ( site_url("page/discussion-board/topic_detail/".$this->input->post("topic_id").'/date/desc/0') );
	    }
		else
		{
			
			$saveData					= array(
												"id"				=> $this->input->post("id"),
											   	"name"				=> $this->input->post("name"),
											    "description"       => $this->input->post("description"),
												"topic_id"			=> $this->input->post("topic_id"),
												"slug"		        => $this->queries->make_slug("tb_posts",
																									   "id",
																									   "slug",
																									   "name",
																									   $this->input->post("name"),
																									 $this->input->post("id")),
												"created_by_user"       => $userid
												);

			
			
			$post_id = $this->input->post('id');
			$old_post_details = $this->queries->fetch_records('getPosts', " AND id='" . $post_id . "'");
			$old_post = $old_post_details->row();

			$this->queries->SaveDeleteTables($saveData, 'e', "tb_posts","id"); 


			$topic_id = $this->input->post("topic_id");

			$post_details = $this->queries->fetch_records('getPosts', " AND id='" . $post_id . "'");
			$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
			$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");
			
			$user = $user_details->row();
			$topic = $topic_details->row();
			$post = $post_details->row();
			
			$forum_details = $this->queries->fetch_records('forums', " AND id='" . $topic->frmid . "'");
			$forum = $forum_details->row();

			/* Mail to Admin */

			$heading = 'A user "' . $user->name . '" has updated post from "'. $old_post->name .'" to "' . $post->name . '" in topic "' . $topic->name . '"' ;

			$email_template = array(
				"email_heading" => $heading,
				"email_file" => "email/frontend/post_email.php",
				"email_subject" => $heading,
				"default_subject" => true,
				"email_post" => array('user' => $user, 'forum' => $forum, 'topic' => $topic, 'old_post'=>$old_post, 'post' => $post,'updated'=>true)
			);

			$is_email_sent = $this->_send_email($email_template);
			/* Mail to Admin */
			
			
			$data['_messageBundle']									= $this->_messageBundle( 'success' , 
																							 "Your post submited Successfully.", 
																							 lang_line("heading_operation_success"),
																							 false, 
																							 true);
	   
 	
	//	$this->_create_fields_for_form(true, $data, $edit_details );	
	
        redirect ( site_url( "page/discussion-board/topic_detail/".$this->input->post("topic_id").'/date/desc/0') );

	   	}

       }else{
       	$data														= $this->data;
		
		#standard validation
		$this->functions->unite_post_values_form_validation();
		
        #To get user id 
        $userid=$this->functions->_user_logged_in_details('id'); 
		
		$this->form_validation->set_rules('name', 'Post', 'trim|required');
		$this->form_validation->set_rules('topic_id', 'Topic Id', 'trim|required');
		
		if( $this->form_validation->run() == FALSE )
		{
		    $data['_topic_id']									=$is_id;
		//	$data['_pageview']									= $this->TMP_dir . $data["_directory"] . "view.php";
			$data['_messageBundle']								=$this->_messageBundle( 'danger' , 
																							 "Fields are empty .", 
																							 lang_line("heading_operation_danger"),
																							 false, 
																							 true);
			redirect ( site_url( "page/discussion-board/topic_detail/".$this->input->post("topic_id").'/date/desc/0') );
	    }
		else
		{
			
			$saveData					= array(
											   	"name"				=> $this->input->post("name"),
											    "description"       => $this->input->post("description"),
												"topic_id"			=> $this->input->post("topic_id"),
												"slug"		        => $this->queries->make_slug("tb_posts",
																									   "id",
																									   "slug",
																									   "name",
																									   $this->input->post("name"),
																									 $this->input->post("id")),
												"created_by_user"       => $userid
												);

			
			$this->queries->SaveDeleteTables($saveData, 's', "tb_posts","id");
			$post_id = $this->db->insert_id();

			$post_details = $this->queries->fetch_records('getPosts', " AND id='" . $post_id . "'");
			$post = $post_details->row();

			$topic_id = $this->input->post("topic_id");

			$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
			$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");
			
			$user = $user_details->row();
			$topic = $topic_details->row();
			
			$forum_details = $this->queries->fetch_records('forums', " AND id='" . $topic->frmid . "'");
			$forum = $forum_details->row();

			/* Mail to Admin */
			$heading = 'A user "' . $user->name . '" has added new post "' . $post->name . '" in topic "' . $topic->name . '"' ;

			$email_template = array(
				"email_heading" => $heading,
				"email_file" => "email/frontend/post_email.php",
				"email_subject" => $heading,
				"default_subject" => true,
				"email_post" => array('user' => $user, 'forum' => $forum, 'topic' => $topic, 'post' => $post)
			);

			$is_email_sent = $this->_send_email($email_template);
			/* Mail to Admin */
			
			$data['_messageBundle']									= $this->_messageBundle( 'success' , 
																							 "Your post submited Successfully.", 
																							 lang_line("heading_operation_success"),
																							 false, 
																							 true);
	   
 	
		// $this->_create_fields_for_form(true, $data, $edit_details );	
	
        redirect (site_url("page/discussion-board/topic_detail/".$this->input->post("topic_id").'/date/desc/0'));

	   	}		
     }




   }


	public function post_comment(){

	$userid=$this->functions->_user_logged_in_details('id'); 
	
	if($userid==0){
		redirect ( site_url("memberlogin") );
     }

        $this->restrictToApprovedPaidImiMembers($userid);

	  $data														= $this->data;
	
		#standard validation
		$this->functions->unite_post_values_form_validation();
		
        #To get user id 
        

        $userid=$this->functions->_user_logged_in_details('id'); 
        
		
		$this->form_validation->set_rules('comment', 'Comment', 'trim|required');
		$this->form_validation->set_rules('post_id', 'Post Id', 'trim|required');
		
		if( $this->form_validation->run() == FALSE )
		{
		//	$data['_pageview']									= $this->TMP_dir . $data["_directory"] . "edit.php";
			/*$data['_messageBundle']								= $this->_messageBundle( 'danger' , 
																							 "Comment field is  required .", 
																							 lang_line("heading_operation_danger"),
																							 false, 
																							 true);
		*/
			redirect ( site_url("page/comments/comments/".$this->input->post("post_id")));
		
		}
		else
		{
			if( $this->input->post('pid') != "")
				$parntid= $this->input->post('pid'); else $parntid=null;
      
       if($parntid==''){

			$saveData					= array(
											   	"comment"				=> $this->input->post("comment"),
											 	"post_id"			    => $this->input->post("post_id"),
											 	"user_id"       		=> $userid
												);

			}else if($this->input->post("replyto")!=''){

			$saveData					= array(
											   	"comment"				=> $this->input->post("comment"),
											 	"post_id"			    => $this->input->post("post_id"),
											 	"parentid"				=> $parntid,
											 	"user_replied_to"		=>$this->input->post("replyto"),
												"user_id"       		=> $userid
												);
			}else{

			$saveData					= array(
			
											   	"comment"				=> $this->input->post("comment"),
											 	"post_id"			    => $this->input->post("post_id"),
											 	"parentid"				=> $parntid,
											 	"user_id"       		=> $userid
			
												);
		} 

		$this->queries->SaveDeleteTables($saveData, 's', "tb_comments","id");

		$post_id = $this->input->post("post_id");

		/* Mail to Admin */
		$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
		$post_details = $this->queries->fetch_records('getPosts', " AND id='" . $post_id . "'");

		$user = $user_details->row();
		$post = $post_details->row();

		$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $post->topic_id . "'");
		$topic = $topic_details->row();

		$forum_details = $this->queries->fetch_records('forums', " AND id='" . $topic->frmid . "'");
		$forum = $forum_details->row();

		$comment = $this->input->post("comment");

		$heading = 'A user "' . $user->name . '" has added new comment in post "' . $post->name . '"';

		$email_template = array(
			"email_heading" => $heading,
			"email_file" => "email/frontend/comment_email.php",
			"email_subject" => $heading,
			"default_subject" => true,
			"email_post" => array('user' => $user, 'forum'=>$forum, 'topic'=>$topic, 'post' => $post, 'comment' => $comment)
		);

		$is_email_sent = $this->_send_email($email_template);
		/* Mail to Admin */

    	$data['_messageBundle']										= $this->_messageBundle( 'success' , 
																							 "Your Comment Posted Successfully.", 
																							 lang_line("heading_operation_success"));
	 		redirect ( site_url( "page/comments/comments/".$this->input->post("post_id")) );
			
	 	}		
	}

	public function delete_topic($forum_id='',$topic_id=''){

	$userid=$this->functions->_user_logged_in_details('id'); 
    
    if($userid==0){
			redirect ( site_url("memberlogin") );
     }

        $this->restrictToApprovedPaidImiMembers($userid);

   	    	$data														= $this->data;
			$saveData['id']									= $topic_id;	
			    
			$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");
			$topic = $topic_details->row();
			
			$this->queries->SaveDeleteTables($saveData, 'd', "tb_forum_topics", 'id') ;

			/* Mail to Admin */
			$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
			$forum_details = $this->queries->fetch_records('forums', " AND id='" . $forum_id . "'");

			$user = $user_details->row();
			$forum = $forum_details->row();

			$heading = 'A user "' . $user->name . '" has delete topic "'. $topic->name .'" in forum "' . $forum->name . '"';
			
			$email_template = array(
				"email_heading" => $heading,
				"email_file" => "email/frontend/topic_email.php",
				"email_subject" => $heading,
				"default_subject" => true,
				"email_post" => array('user' => $user, 'forum' => $forum, 'topic' => $topic,'delete'=>true)
			);

			$is_email_sent = $this->_send_email($email_template);
			/* Mail to Admin */

           redirect ( site_url("page/discussion-board",$data) );
    }


	public function delete_post( $topic_id,$post_id='', $column='date'){

		$userid=$this->functions->_user_logged_in_details('id'); 

      if($userid==0){
		redirect ( site_url("memberlogin") );
      }

        $this->restrictToApprovedPaidImiMembers($userid);

            $sortby=$column;
         //   echo $sortby;  die;
   	    	$data														= $this->data;
			$saveData['id']									= $post_id;	
			
			$post_details = $this->queries->fetch_records('getPosts', " AND id='" . $post_id . "'");
			$post = $post_details->row();
			
			$this->queries->SaveDeleteTables($saveData, 'd', "tb_posts", 'id') ;


	  		/* Mail to Admin */
			$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
			$topic_details = $this->queries->fetch_records('getforumtopics', " AND id='" . $topic_id . "'");

			$user = $user_details->row();
			$topic = $topic_details->row();

			$forum_details = $this->queries->fetch_records('forums', " AND id='" . $topic->frmid . "'");
			$forum = $forum_details->row();

			$heading = 'A user "' . $user->name . '" has delete post "'. $post->name .'" in topic "' . $topic->name . '"';
			
			$email_template = array(
				"email_heading" => $heading,
				"email_file" => "email/frontend/post_email.php",
				"email_subject" => $heading,
				"default_subject" => true,
				"email_post" => array('user' => $user, 'forum' => $forum, 'topic' => $topic, 'post'=>$post,'delete'=>true)
			);

			$is_email_sent = $this->_send_email($email_template);
			/* Mail to Admin */

            redirect (site_url("page/discussion-board/topic_detail/".$topic_id."/".$sortby."/asc/".'0'));
    }

    public function event_joining($eventid=''){
	

	$userid=$this->functions->_user_logged_in_details('id'); 
    
	    if($userid==0){
			redirect ( site_url("memberlogin") );
	    }
	    
	    $data							= $this->data;
	
	    $data['is_joining_event']		= TRUE;
		
		$data['eventdetail']			= $this->queries->fetch_records('getEventDetail', " AND id = {$eventid}");
		
		if ( $data['eventdetail'] -> num_rows() <= 0 )
		{
			show_404();
		}
		
	
		$data['content_detail']			 = $data['eventdetail']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
		
		$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
    }
   
    public function event_form(){

		 	$data                                     =$this->data;
            $userid=$this->functions->_user_logged_in_details('id');

			     if($userid==0){
						redirect ( site_url("memberlogin") );
				    }
	    
    		$saveData=array();
			    		
			    		$saveData['eventid'] = (int)$this->input->post("eventid");
			    		$saveData['userid']  = (int)$userid;
			    		$saveData['event']   = $this->input->post("event");
			
			$isjoined=$this->queries->fetch_records('whojoinevents', " AND eventid = '".$saveData['eventid']."' AND userid='".$saveData['userid']."'");
			
			if($isjoined->num_rows()>0){
			
			$query="UPDATE tb_join_event set event='".$saveData['event']."' WHERE eventid=".$saveData['eventid']." AND userid=".$saveData['userid']."";
			$this->db->query($query);
			
			}else{
            
				$this->queries->SaveDeleteTables($saveData, 's', "tb_join_event","id");
			
				/* Mail to Admin */
				/*
				$user_details = $this->queries->fetch_records_imiconf('users', " AND id='" . $userid . "'");
				$event_details = $this->queries->fetch_records('sitesectionswidgets', " AND id='" . $saveData['eventid'] . "'");

				$user = $user_details->row();
				$event = $event_details->row();
				
				$heading = 'A user "' . $user->name . '" wants to join event "' . $event->title . '"';
				
				$email_template = array(
					"email_heading" => $heading,
					"email_file" => "email/frontend/event_email.php",
					"email_subject" => $heading,
					"default_subject" => true,
					"email_post" => array('user'=> $user,'event'=>$event,'join_status'=>$saveData['event'])
				);

				$is_email_sent = $this->_send_email($email_template);*/
				/* Mail to Admin */
       		}
       		
       		$data['_messageBundle']										= $this->_messageBundle( 'success' , 
																							 "Thank You React On Event.", 
																							 lang_line("heading_operation_success"));
	
 		


		    $data['is_joining_event']		= TRUE;
			
			$data['eventdetail']			= $this->queries->fetch_records('getEventDetail', " AND id = ".$saveData['eventid']."");
			$slug=$data['eventdetail']->row()->slug;
		/*	echo $slug;
			die;
			redirect(site_url().'/upcoming-events/events/'.$slug.'.html');


		*/

				$data['content_detail']			 = $data['eventdetail']; #$this->queries->fetch_records('cmscontent', " AND menuid = '". $data['menu_detail']->row("id") ."' ");
				

				$this->_call( $data, WIDGET_CMSSECTION_ACTIVITIESEVENTS );
			
    }

    protected function restrictToApprovedPaidImiMembers($userid)
    {
        if (!$this->functions->validate_if_user_is_a_paid_member($userid)) {
            $this->_messageBundle('warning', 
				lang_line('text_not_auth_desc'), 
				lang_line('text_not_auth_head'), 
				false, 
				true
			);
            redirect(site_url("account/myprofile/controls/view"));
        }
	}

	public function payeezy_recurring_payment()
	{
		$payments = $this->db->query(" SELECT df.*, dp.id as dpid, dcp.card_expiry, dcp.card_type, dcp.card_name, dcp.token, dcp.trans_recur_id, dcp.id as dcpid FROM ((`tb_donation_form` df INNER JOIN `tb_donation_payments` dp ON df.id = dp.table_id_value AND dp.table_name = 'tb_donation_form') INNER JOIN `tb_card_payments` dcp ON dp.id = dcp.payment_id) WHERE df.donation_mode = 'recurring' and df.is_paid = 1 and df.cancelled = 0 and dp.payment_mode = 'payeezy' and dcp.is_cron = 0 ")->result_array();
		$success = $failure = 0;
		$successData = $failData = array();
		$this->load->library('payeezy');
		$this->load->library('pdf');
		$this->load->library('custom_log');

		foreach($payments as $payment) {
			if($payment['donation_freq'] == 'Y-1'){
				$freq = '12';
			} else {
				$freq = substr($payment['donation_freq'], 2);
			}
			if(!empty($payment['last_recurring_payment']) AND $payment['num_of_recurring'] > 0){
				$lastDate = $payment['last_recurring_payment'];
			} else {
				$lastDate = $payment['date_added'];
			}
			if(strtotime("now") >= strtotime("+ ".$freq." months", strtotime($lastDate))){
			//if(strtotime("now") >= strtotime($lastDate)){
				$data = array(
					'card_expiry' => $payment['card_expiry'],
					'card_type' => $payment['card_type'],
					'card_name' => $payment['card_name'],
					'ta_token' => $payment['token'],
					'transaction_id' => $payment['trans_recur_id'],
					'amount' => $payment['donate_amount'],
					'email' => $payment['email']
				);
				$pay = new Payeezy();
				$resultData = $pay->pay_recurring($data);

				if (isset($resultData['error'])) { 
					$logging = new Custom_log;
					$logging->write_log('error', __FILE__.' '.$resultData['error']. " line " .__LINE__.' '.print_r($resultData['request'],true));
			    }
				if(!isset($resultData) || !is_array($resultData)){
					unset($pay);
					continue;
				} else {
					$donationId = $payment['id'];
					
					$editData = array();
					$emailArray = array(
						'name' => $payment['card_name'],
						'amount' => $payment['donate_amount']
					);
					if ( isset($resultData['success'] ) ){
						$editData['num_of_recurring'] = $payment['num_of_recurring']+1;
						$editData['last_recurring_payment'] = date("Y-m-d");
						$editData['id'] = $donationId;
						$this->queries->SaveDeleteTables($editData, 'e', "tb_donation_form", 'id');
						
						$success++;
						$emailArray['auth'] 			= $resultData['response']->Authorization_Num;
						$emailArray['payment_no'] 		= $editData['num_of_recurring'];
						$successData[$success] 			= $emailArray;
						$payment['num_of_recurring'] 	= $editData['num_of_recurring'];

						$_POST["recurring"]				= true;
					}

					$saveData		= array(
						"payment_id"		=> $payment['dpid'],
						"card_name"			=> $payment['card_name'],
						'card_type'			=> isset($resultData['response']->CardType ) ? $resultData['response']->CardType : $payment['card_type'],
						'card_expiry'		=> $payment['card_expiry'],
						'ref_no'			=> isset($resultData['response']->Retrieval_Ref_No ) ? $resultData['response']->Retrieval_Ref_No : '',
						'amount'			=> isset($resultData['response']->DollarAmount ) ? $resultData['response']->DollarAmount : '',
						'currency'			=> isset($resultData['response']->Currency ) ? $resultData['response']->Currency : '',
						'transaction_tag'	=> isset($resultData['response']->Transaction_Tag ) ? $resultData['response']->Transaction_Tag : '',
						'ctr'				=> isset($resultData['response']->CTR ) ? $resultData['response']->CTR : '',
						'transaction_id'	=> isset($resultData['response']->Authorization_Num ) ? $resultData['response']->Authorization_Num : '',
						'sequence_no'		=> isset($resultData['response']->SequenceNo ) ? $resultData['response']->SequenceNo : '',
						'is_cron'			=> 1,
						'date_added'		=> date("Y-m-d H:i:s"),
					);

					if(isset($resultData['response']->CTR)){
						unset($resultData['response']->CTR);
					}
					if(isset($resultData['request']['Card_Number'])){
						unset($resultData['request']['Card_Number']);
					}
					$saveData['payeezy_post'] 		= isset($resultData['response'] ) ? json_encode($resultData['response']) : '';
					$saveData['request_data'] 		= isset($resultData['request'] ) ? json_encode($resultData['request']) : '';
					$saveData['token'] 			= isset($resultData['response']->TransarmorToken ) ? $resultData['response']->TransarmorToken : '';
					$saveData['trans_recur_id'] = isset($resultData['response']->StoredCredentials->TransactionId ) ? $resultData['response']->StoredCredentials->TransactionId : '';


					$this->queries->SaveDeleteTables($saveData, 's', "tb_card_payments", 'id');

					if ( isset($resultData['error'] ) ){
						$failure++;
						$emailArray['error'] = $resultData['error'];
						$emailArray['cancel_url'] = site_url("admincms/viewdonation/controls/view/0/".$donationId);
						$emailArray['payment_no'] = ($payment['num_of_recurring']+1);
						$failData[$failure] = $emailArray;
						continue;
					}
					#*************************** *************************** *************************** 
					#*************************** 	    EMAIL PREPARATION 	 *************************** 
					#*************************** *************************** *************************** 
			
					#to_user
					$_POST["TEXT_p"]				= 'Dear ' . $payment["first_name"] . ' ' . $payment["last_name"] . ',
													<br /> <br />Thank you for donating <br><br>
													Donate Date: ' . date("d-m-Y");
													
					$_POST["donation_details"]		= $payment;
												
											
					$email_template					= array("email_to"				=> $payment["email"],
															"email_heading"			=> "Donation Form",
															"email_file"			=> "email/frontend/donate_form.php",
															"email_subject"			=> "Donation Form",
															"default_subject"		=> TRUE,
															"email_post"			=> $_POST
															);
					
					$is_email_sent					= $this->_send_email( $email_template );
					#to_user
					
					
					// create tax receipt pdf and send it to user.
					$this->functions->send_tax_receipt($donationId, $this, true);
					
					
					#test email
					$message 					= '<strong>DONATE FORM:</strong> test _ payment ' . serialize( $_POST ) ;
					$email_template				= array("email_to"				=> 'ali.nayani@genetechsolutions.com',
														"email_heading"			=> 'DONATION FORM',
														"email_file"			=> "email/global/_blank_page.php",
														"email_subject"			=> '---> DONATION FORM',
														"email_post"			=> array("content"		=> $message) );
					
					//$is_email_sent				= $this->_send_email( $email_template );
					#test email
					
					
					
					#*************************** *************************** *************************** 
					#*************************** 	    EMAIL PREPARATION 	 *************************** 
					#*************************** *************************** *************************** 

					unset($_POST);
					unset($pay);
					continue;
				}
			}
		}
		if($success > 0 || $failure > 0){
			#*************************** *************************** *************************** 
			#*************************** 	    EMAIL PREPARATION 	 *************************** 
			#*************************** *************************** *************************** 
	
			#to_user
			$_POST["TEXT_p"]					= 'Dear Admin,
													<br /> <br />Here are the Recurring Donataion Details For Date: ' . date("d-m-Y");
											
			$_POST["details"]['success']		= $success;
			$_POST["details"]['successdata']	= $successData;
			$_POST["details"]['failure']		= $failure;
			$_POST["details"]['faildata']		= $failData;
										
									
			$email_template						= array(//"email_to"									=> "ali.nayani@genetechsolutions.com",
													"email_heading"									=> "Donation Form",
													"email_file"									=> "email/frontend/donate_form_admin.php",
													"email_subject"									=> "Donation Form",
													"default_subject"								=> TRUE,
													"email_post"									=> $_POST
													);
			
			$is_email_sent					= $this->_send_email( $email_template );
			#to_user			
			
			
			#*************************** *************************** *************************** 
			#*************************** 	    EMAIL PREPARATION 	 *************************** 
			#*************************** *************************** *************************** 
		}
		die('==END==');
	}
}