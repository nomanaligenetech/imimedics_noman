<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class PP_New_Donate_Form extends C_frontend {

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
	
	public function __construct( &$data, $ci )
	{
//	    ~r(debug_backtrace());
		//$this=$ci;
		#parent::__construct();
		
		$ci->load->library('payeezy');
		$ci->load->library('pdf');
		$ci->load->library('custom_log');

		#ALL VARIABLES WILL BE ASSOCIATE WITH __UNIQUE VARIABLE.. THIS HELPS IN MULTIPLE FORMS ON SAME PAGE HAVING SAME VARIABLE LIKE 'address', 'first_name' ETC. 
		#WE CAN DISTINGUISH THEM WITH THIS __UNIQUE
		$data['__UNIQUE']																= "DONATEFORM";
		$data[ $data['__UNIQUE'] ]["_messageBundle_redirect_paypal"]					= $ci->_messageBundle( 'info' , '<p>You are now redirected to Paypal</p>', "Please Wait...");
		PP_New_Donate_Form::extend_data( $data );

	}
	
	
	static public function extend_data( &$data )
	{
		
		
		$data[ $data['__UNIQUE'] ]['_process_to_paypal']								= FALSE;
		$data[ $data['__UNIQUE'] ]['_is_recurring']										= FALSE;
		
		$data[ $data['__UNIQUE'] ]['address']											= FALSE;
		$data[ $data['__UNIQUE'] ]['city']												= FALSE;
		$data[ $data['__UNIQUE'] ]['country']											= FALSE;
		$data[ $data['__UNIQUE'] ]['state']												= FALSE;
		$data[ $data['__UNIQUE'] ]['zip']												= FALSE;

		$data[$data['__UNIQUE']]['sehm_childs'] = unserialize(lang_line('dropdown_sehm_childs'));	
		$data[$data['__UNIQUE']]['syed_childs'] = unserialize(lang_line('dropdown_syed_childs'));
		
	}
	
	
	
	
	static function _create_fields_for_form( $return_array = false, &$data, $db_data = array() )
	{
		
		$empty_inputs				= array( "id", "first_name", "last_name", "email" );
		
		
		
		$filled_inputs				= array( "id", "name", "last_name", "email");
		
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
						case "default_date":	
							$tmp_value			= date("d-m-Y", strtotime( $db_data[ $filled_inputs[$x] ] ) );
							break;
							
						case "default":	
							break;
					}
				}
				
				$data[ $data['__UNIQUE'] ][ $empty_inputs[$x] ]		= $tmp_value;
			
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
				
				$data[ $data['__UNIQUE'] ][ $empty_inputs[$x] ]		= $tmp_value;
			}
			
			return $data;
		
		}
	}
	
	
	static public function payment_success(  &$data = array(), $ci  )
	{

		$is_post_get								= FALSE;
		$is_post_get_data							= array();
		$process_payment							= FALSE;
		$payment_mode								= "paypal";	
		
		if ( count($ci->input->post()) > 1 )
		{
			$is_post_get								= TRUE;
			$is_post_get_data 							= $ci->input->post();
			$custom										= $is_post_get_data["custom"];
			$payment_status								= $is_post_get_data["payment_status"];
			$payer_id									= $is_post_get_data["payer_id"];
		}
		
		if ( count($ci->input->get()) > 1 )
		{
			$is_post_get								= TRUE;
			$is_post_get_data 							= $ci->input->get();
			$custom										= $is_post_get_data["cm"];
			$payment_status								= $is_post_get_data["st"];
			$payer_id									= $is_post_get_data["tx"];
		}

		if ( $is_post_get  )
		{
			$TMP_explode_strings							= explode("|", $custom); //0: reference number, 1: donation_form id
			$TMP_check_if_already_paid_for_donation_id		= $ci->db->query("SELECT * FROM tb_donation_payments WHERE 
																				table_name = '". $TMP_explode_strings[0] ."' AND 
																				table_id_name = '". $TMP_explode_strings[1] ."' AND 
																				table_id_value = '". $TMP_explode_strings[2] ."' AND user_id = '". $is_post_get_data["item_number"] ."' ");
			
			if ( count($TMP_explode_strings) != 3 )
			{
				if(!empty($_GET['tx']) && !empty($_GET['amt']) && $_GET['st'] == 'Completed'){ 
					$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like");
			
					$data['_pageview']						= "global/_blank_page.php";	
						
				}else{
					$data['_messageBundle']					= $ci->_messageBundle( 	'danger_big' , 
																					"You have not submiited Donation Form correctly. 
																					The payment cannot be process further. In-sufficient details.", 
																					"Process Failed");
					
					$data['_pageview']						= "global/_blank_page.php";	
				}
			}
			// else if ( $TMP_check_if_already_paid_for_donation_id -> num_rows() > 0 )
			// {
			// 	$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
			// 					"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
			// 					"You’re what making a difference looks like");
			
			// 	$data['_pageview']						= "global/_blank_page.php";	
			// }
			else
			{
				$process_payment						= TRUE;
			}
			
		}
		else
		{
			$data['_messageBundle']						= $ci->_messageBundle( 	'danger_big' , 
																				"Invalid Donation Payment. <strong>Contact Administrator</strong>", 
																				"Process Failed");
			
			$data['_pageview']							= "global/_blank_page.php";		
		}
		
		
		
		// var_dump($process_payment);die('hshdsj');
		
		if ( $process_payment )
		{
			$TMP_table										= $ci->db->query("SELECT df.*, dcc.comment as comments FROM tb_donation_form df LEFT JOIN `tb_dc_comments` dcc ON df.id = dcc.df_id WHERE df.id = '". $TMP_explode_strings[2] ."'");
			
			$TMP_payment_gross								= "onetime";
			$TMP_ipn_track_id								= $is_post_get_data["ipn_track_id"] ? $is_post_get_data["ipn_track_id"] : 0;
			$TMP_payment_status								= $payment_status;
			if ( $TMP_table->row()->donation_mode == "recurring" )
			{
				$TMP_payment_gross							= "amount3";
				$TMP_ipn_track_id							= rand(0, 300);
				$TMP_payment_status							= "Completed";
			}
			
			
		
			$saveData = array(
				"user_id"								=> $is_post_get_data["item_number"],
				"payer_email"							=> $is_post_get_data["payer_email"] ? $is_post_get_data["payer_email"] : "",
				"payment_status"						=> $TMP_payment_status,
				"payment_mode"							=> $payment_mode,
				"table_name"							=> $TMP_explode_strings[0],
				"table_id_name"							=> $TMP_explode_strings[1],
				"table_id_value"						=> $TMP_explode_strings[2],
				"date_added"							=> date("Y-m-d H:i:s"),
				'reference_number'						=> $is_post_get_data['txn_id'] ? $is_post_get_data['txn_id'] : ""
			);
				
				
			$ci->queries->SaveDeleteTables($saveData, 's', "tb_donation_payments", 'id');
			$payment_id = $ci->db->insert_id();

			$saveData = array(
				"payment_id"							=> $payment_id,
				"payment_gross"							=> $TMP_payment_gross,
				"ipn_track_id"							=> $TMP_ipn_track_id,
				"payer_id"								=> $payer_id,
				"paypal_post"							=> serialize( $is_post_get_data ),
			);
				
				
			$ci->queries->SaveDeleteTables($saveData, 's', "tb_paypal_payments", 'id');
			
			
			
			
			
		
			
			if ( $TMP_table -> num_rows() > 0 )
			{
				$TMP_data										= array("id"									=> $TMP_explode_strings[2],
																		"is_paid "								=> 1); 
					
				
				
				$ci->queries->SaveDeleteTables($TMP_data, 'e', "tb_donation_form", 'id'); 
				
				
				
				#*************************** *************************** *************************** 
				#*************************** 	    EMAIL PREPARATION 	 *************************** 
				#*************************** *************************** *************************** 
		
				#to_user
				$_POST["TEXT_p"]				= 'Dear ' . $TMP_table->row()->first_name . ' ' . $TMP_table->row()->last_name . ',
												   <br /> <br />Thank you for donating <br><br>
												   Donate Date: ' . date("d-m-Y", strtotime($saveData['date_added']) );
												   
				$_POST["donation_details"]		= $TMP_table->row_array();
											
										
				$email_template					= array("email_to"										=> $TMP_table->row()->email,
														"email_heading"									=> "Donation Form",
														"email_file"									=> "email/frontend/donate_form.php",
														"email_subject"									=> "Donation Form",
														"default_subject"								=> TRUE,
														"email_post"									=> $_POST
														);
				
				$is_email_sent				= $ci->_send_email( $email_template );
				#to_user
				
				// create tax receipt pdf and send it to user.
				// self::send_tax_receipt($TMP_explode_strings[2], $ci);	
				$ci->functions->send_tax_receipt($TMP_explode_strings[2], $ci);
				
				
				#test email
				$message 					= '<strong>DONATE FORM:</strong> test _ payment ' . serialize( $_POST ) ;
				$email_template				= array("email_to"				=> 'sadiq.hussain@genetechsolutions.com',
													"email_heading"			=> 'DONATION FORM',
													"email_file"			=> "email/global/_blank_page.php",
													"email_subject"			=> '---> DONATION FORM',
													"email_post"			=> array("content"		=> $message) );
				
				//$is_email_sent				= $ci->_send_email( $email_template );
				#test email
				
				
				
				#*************************** *************************** *************************** 
				#*************************** 	    EMAIL PREPARATION 	 *************************** 
				#*************************** *************************** *************************** 
				
				
				#$_POST["_pageview"]							= "global/_blank_page.php";
				// $data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
				// 				"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
				// 				"You’re what making a difference looks like");
				
				// $data['_pageview']							= "global/_blank_page.php";

				$data['custom_donate_ty_data']	= array(
					'title'		=> 'You’re what making a difference looks like',
					'message'	=> 'Thank you for your gift. We will send you the tax receipts.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612',
				);

				$data['_pageview']						= "global/_custom_donate_thankyou_page.php";
				$data['is_donation_success']			    = "yes";

				#redirect( site_url( uri_string() ) );
			}
				
		}
		
		
	
	}

        static public function payment_success_honor(  &$data = array(), $ci  )
	{

		$is_post_get								= FALSE;
		$is_post_get_data							= array();
		$process_payment							= FALSE;
		$payment_mode								= "paypal";	
		
		if ( count($ci->input->post()) > 1 )
		{
			$is_post_get								= TRUE;
			$is_post_get_data 							= $ci->input->post();
			$custom										= $is_post_get_data["custom"];
			$payment_status								= $is_post_get_data["payment_status"];
			$payer_id									= $is_post_get_data["payer_id"];
		}
		
		if ( count($ci->input->get()) > 1 )
		{
			$is_post_get								= TRUE;
			$is_post_get_data 							= $ci->input->get();
			$custom										= $is_post_get_data["cm"];
			$payment_status								= $is_post_get_data["st"];
			$payer_id									= $is_post_get_data["tx"];
		}
		
		if ( $is_post_get  )
		{
			$TMP_explode_strings							= explode("|", $custom); //0: reference number, 1: donation_form id

			$TMP_check_if_already_paid_for_donation_id		= $ci->db->query("SELECT * FROM tb_donation_payments WHERE 
				table_name = '". $TMP_explode_strings[0] ."' AND 
				table_id_name = '". $TMP_explode_strings[1] ."' AND 
				table_id_value = '". $TMP_explode_strings[2] ."' AND user_id = '". $is_post_get_data["item_number"] ."' ");
			
			if ( count($TMP_explode_strings) != 3 )
			{
				if(!empty($_GET['tx']) && !empty($_GET['amt']) && $_GET['st'] == 'Completed'){ 
					$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612",
								"You’re what making a difference looks like");
					$data['_pageview']						= "global/_blank_page.php";	
				}else{
					$data['_messageBundle']					= $ci->_messageBundle( 	'danger_big' , 
						"You have not submiited Donation Form correctly. 
						The payment cannot be process further. In-sufficient details.", 
						"Process Failed");
					
					$data['_pageview']						= "global/_blank_page.php";	
				}
			}
			else if ( $TMP_check_if_already_paid_for_donation_id -> num_rows() > 0 )
			{
				$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like");
				$data['_pageview']						= "global/_blank_page.php";	
			}
			else
			{
				$process_payment						= TRUE;
			}
			
		}
		else
		{
			$data['_messageBundle']						= $ci->_messageBundle( 	'danger_big' , 
				"Invalid Donation Payment. <strong>Contact Administrator</strong>", 
				"Process Failed");
			
			$data['_pageview']							= "global/_blank_page.php";		
		}
		
		
		
		
		
		if ( $process_payment )
		{
			$TMP_table										= $ci->db->query("SELECT * FROM tb_give_honor_someone WHERE id = '". $TMP_explode_strings[2] ."' ");
			
			$TMP_payment_gross								= "onetime";
			$TMP_ipn_track_id								= $is_post_get_data["ipn_track_id"] ? $is_post_get_data["ipn_track_id"] : 0;
			$TMP_payment_status								= $payment_status;

			$saveData		= array(
				"user_id"		=> $is_post_get_data["item_number"],
				"payer_email"	=> $is_post_get_data["payer_email"] ? $is_post_get_data["payer_email"] : "",
				"payment_status"=> $TMP_payment_status,
				"payment_mode"	=> $payment_mode,
				"table_name"	=> $TMP_explode_strings[0],
				"table_id_name"	=> $TMP_explode_strings[1],
				"table_id_value"=> $TMP_explode_strings[2],
				"date_added"	=> date("Y-m-d H:i:s"),
				'reference_number' => $is_post_get_data['txn_id'] ? $is_post_get_data['txn_id'] : ""
			);

			$ci->queries->SaveDeleteTables($saveData, 's', "tb_donation_payments", 'id');
			$payment_id = $ci->db->insert_id();

			$saveData		= array(
				"payment_id"	=> $payment_id,
				"payment_gross"	=> $TMP_payment_gross,
				"ipn_track_id"	=> $TMP_ipn_track_id,
				"payer_id"		=> $payer_id,
				"paypal_post"	=> serialize($is_post_get_data)
			);


			$ci->queries->SaveDeleteTables($saveData, 's', "tb_paypal_payments", 'id');
			
			
			$TMP_data		= array("id"	=> $TMP_explode_strings[2],
									"is_paid "	=> 1);

			$ci->queries->SaveDeleteTables($TMP_data, 'e', "tb_give_honor_someone", 'id');
				
			#*************************** *************************** *************************** 
			#*************************** 	    EMAIL PREPARATION 	 *************************** 
			#*************************** *************************** *************************** 


			$toname = $TMP_table->row()->send_to == 'honoree' ? $TMP_table->row()->name : $TMP_table->row()->recipientname;
			$toemail = $TMP_table->row()->send_to == 'honoree' ? $TMP_table->row()->honoree_email : $TMP_table->row()->email;
			$email_data = $TMP_table->row_array();
			$email_data["donating_amount"] = $TMP_table->row()->donate_amount;
			$email_data["honorto"] = $TMP_table->row()->name;
			$email_data["sendTo"] = $TMP_table->row()->send_to;
			$email_data["recipientName"] = $TMP_table->row()->recipientname;
			$email_template				= array("email_to"=>$toemail,//$TMP_table->row()->email,
								"email_heading"			=> "Your gift from ".$TMP_table->row()->fromname ,
								"donating_amount"		=> $TMP_table->row()->donate_amount,
								"toname"				=> $toname,
								"fromname"				=> $TMP_table->row()->fromname,
								"emailtosender"			=> FALSE,
								"message"				=> $TMP_table->row()->message,
								"email_file"			=> "email/frontend/give_honor.php",
								"email_subject"			=> "Your gift from ".$TMP_table->row()->fromname,
								"default_subject"		=> TRUE,
								"email_post"			=> $email_data,
							);

			$is_email_sent				= $ci->_send_email( $email_template );



			$email_template2				= array("email_to"=>$TMP_table->row()->card_email,
								"email_heading"			=> "Gift sent to ". $toname,
								"donating_amount"		=> $TMP_table->row()->donate_amount,
								"toname"				=> $toname,
								"fromname"				=> $TMP_table->row()->fromname,
								"emailtosender"			=> TRUE,
								"message"				=> $TMP_table->row()->message,
								"email_file"			=> "email/frontend/give_honor.php",
								"email_subject"			=> "Gift sent to ". $toname,
								"default_subject"		=> TRUE,
								"email_post"			=> $email_data,
							);

			$is_email_sent				= $ci->_send_email( $email_template2 );
			
			#*************************** *************************** *************************** 
			#*************************** 	    EMAIL PREPARATION 	 *************************** 
			#*************************** *************************** *************************** 
				
			#$_POST["_pageview"]							= "global/_blank_page.php";
			$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like");
			
			$data['_pageview']							= "global/_blank_page.php";	
			$data['is_donation_success']			    = "yes";
			#redirect( site_url( uri_string() ) );
		}
	}
	
	static public function payment_cancel(  &$data = array(), $ci  )
	{
		$data['_messageBundle']					= $ci->_messageBundle( 	'danger_big' , 
																		"You have cancelled the Paypal Payment Process.", 
																		"Process Cancelled");
		
		$data['_pageview']						= "global/_blank_page.php";
		
		unset($_POST);
	}
	
	static public function show( &$data = array(), $ci )
	{		
		$__ = false;
		if ( in_array("payment_success", $ci->uri->segments ) )
		{
			PP_New_Donate_Form::payment_success( $data, $ci );
		}
		else if ( in_array("payment_notify", $ci->uri->segments ) )
		{
			PP_New_Donate_Form::payment_success( $data, $ci );
		}
		else if ( in_array("payment_success_honor", $ci->uri->segments ) )
		{
			PP_New_Donate_Form::payment_success_honor( $data, $ci );
		}
		else if ( in_array("payment_notify_honor", $ci->uri->segments ) )
		{
			PP_New_Donate_Form::payment_success_honor( $data, $ci );
		}
		else if ( in_array("payment_cancel", $ci->uri->segments ) )
		{
			PP_New_Donate_Form::payment_cancel( $data, $ci );
		}
		else
		{
			$country_name = self::getIPLocation();
			
			$data['locationID'] = $ci->db->query("SELECT id FROM `tb_countries` WHERE countries_name = '{$country_name}' ")->row()->id;

			$data['chapter_countries'] = $ci->db->query('SELECT `tb_belongs_country`.id, `tb_belongs_country`.country_id, `tb_chapter_paypal_settings`.paypal_email,`tb_currencies`.code,`tb_belongs_country`.country_title, tb_countries.countries_iso_code_2 as iso_code_2 FROM `tb_belongs_country` INNER JOIN `tb_chapter_paypal_settings` ON `tb_belongs_country`.chapter_id = `tb_chapter_paypal_settings`.chapter_id INNER JOIN `tb_currencies` ON `tb_chapter_paypal_settings`.`currency_id` = `tb_currencies`.id inner join tb_countries  on tb_countries.id = `tb_belongs_country`.country_id   ')->result_array();

			if( is_countryCheck(FALSE,FALSE,TRUE) == 'canada' ){
				$data['chapter_countries'] = array_reverse($data['chapter_countries']);
			}

			$ipCheck = $ci->db->query('SELECT ip_address from tb_donatepage_blocked WHERE ip_address = "'.$_SERVER["REMOTE_ADDR"].'"');

			if($ipCheck->row()->ip_address){
				redirect( site_url() );
			}

			$ipCheck = $ci->db->query('SELECT COUNT(*) as totalhits from tb_donatepage_ips WHERE ip_address = "'.$_SERVER["REMOTE_ADDR"].'" AND is_resolved = 0 AND entrytime >= "'.strtotime('-30 minutes', $ci->session->now).'" ');
			if($ipCheck->row()->totalhits > 25){
				$insertData = array(
					'ip_address' => $_SERVER["REMOTE_ADDR"],
					'block_time' => $ci->session->now
				);
				$ci->queries->SaveDeleteTables($insertData, 's', "tb_donatepage_blocked", 'id');
				$message 					= 'Following Ip has been blocked to visit Donate Page:<strong> ' . $_SERVER["REMOTE_ADDR"] . ' </strong>. If you want to unblock please visit backend of IMI <a href="'.site_url("admincms/managedonateblockedip/controls/view").'">here</a>.';
				$email_template				= array("email_heading"			=> 'DONATION Page Ip Blocked',
													"email_file"			=> "email/global/_blank_page.php",
													"email_subject"			=> 'Ip blocked',
													"email_post"			=> array("content" => $message) );
				
				$is_email_sent				= $ci->_send_email( $email_template );
				redirect( site_url() );
			}			
			
			if($ci->input->post("btn_donate_form_new") == false && $data['_pageview'] != "global/_blank_page.php"){
				$insertData = array(
					'ip_address' => $_SERVER["REMOTE_ADDR"],
					'entrytime'	=> $ci->session->now
				);
				$ci->queries->SaveDeleteTables($insertData, 's', "tb_donatepage_ips", 'id');
			}
			
			PP_New_Donate_Form::_create_fields_for_form(false, $data);
		
			
			$edit_details												= $ci->imiconf_queries->fetch_records_imiconf("users", 
																													  " AND id = '". $ci->functions->_user_logged_in_details( "id" ) ."' ");
			
			
			if ( $edit_details -> num_rows() > 0 )
			{
				PP_New_Donate_Form::_create_fields_for_form(true, $data, $edit_details->row_array() );	
				
				
				
				
				$users_profile											= $ci->imiconf_queries->fetch_records_imiconf("users_profile", " AND userid = '". $edit_details->row()->id ."' ");
				if ( $users_profile	-> num_rows() > 0 )
				{
					$data[ $data['__UNIQUE'] ]['address']									= $users_profile->row()->home_full_address;
					$data[ $data['__UNIQUE'] ]['city']										= $users_profile->row()->home_city;
					$data[ $data['__UNIQUE'] ]['country']									= $users_profile->row()->home_country_name;
					$data[ $data['__UNIQUE'] ]['state']										= $users_profile->row()->home_state_province;
				}
				
			}
			
				$siteIdQuery	= getSiteId();
				$columns_data	= $ci->queries->fetch_records("donation_give_on_will", $siteIdQuery . " order by id desc")->result()[0];
				
					
				if ( $columns_data)
				{
					$donation_ways_to_give_languages = $ci->queries->fetch_records("donation_ways_to_give_languages", " AND donation_ways_to_give_id = {$columns_data->id}")->result_array();
					replace_data_for_lang($columns_data, $data['content_languages'], $donation_ways_to_give_languages, ['column_first_text','column_two_text','column_three_text','donation_way_to_give_address'], SessionHelper::_get_session('LANG_CODE') );
					$data[ $data['__UNIQUE'] ]['column_first_text']							= $columns_data->column_first_text;
					$data[ $data['__UNIQUE'] ]['column_two_text']							= $columns_data->column_two_text;
					$data[ $data['__UNIQUE'] ]['column_three_text']							= $columns_data->column_three_text;
					$data[ $data['__UNIQUE'] ]['donation_way_to_give_address']				= $columns_data->donation_way_to_give_address;
				}
			
			$data['_messageBundle_please_login']						= $ci->_messageBundle( 'loginalert' , lang_line("loginalert_incms") );
			$data[ $data['__UNIQUE'] ]["_messageBundle_not_a_member"]						= $ci->_messageBundle( 'info' , '<p>You must be a IMI-Member to submit the form</p>');
			// if($_SERVER['REMOTE_ADDR'] == '103.159.24.34'){
			// 	// var_dump($edit_details -> num_rows());
			// 	die('sd445gvasduyddg');
			// 	}
			return $ci->load->view( "frontend/cms/page_plugins/pp_donate_form_new", $data, TRUE );
		}
		
	}
	
	
	static public function index( &$data = array(), $ci )
	{
		
		PP_New_Donate_Form::extend_data( $data );

		$insertData = array(
						'ip_address' => $_SERVER["REMOTE_ADDR"],
						'entrytime'	=> $ci->session->now,
						'is_submit' => 1
					);
		$ci->queries->SaveDeleteTables($insertData, 's', "tb_donatepage_ips", 'id');

		//If Give In Honor Of Someone
		if($ci->input->post("btn_donate_form_new")=="Send")
		{
			

			$_POST['donation_mode'] = "onetimepay";
			$_POST['donation_projects'] = "IMI Donation In Honor Of Someone";

			$sendTo = null == $ci->input->post('sendTo') || $ci->input->post('sendTo') == "" ? 'honoree' : $ci->input->post('sendTo');
			$sendType = null == $ci->input->post('sendType') || $ci->input->post('sendType') == "" ? 'now' : $ci->input->post('sendType');
			$recipientName = '';
			

			$ci->form_validation->set_rules('honorto', lang_line('text_inhonorof'), 'trim|required');
			$ci->form_validation->set_rules('fromname', lang_line('label_from_name'), 'trim|required');

			$ci->form_validation->set_rules('honoreeEmail', lang_line('text_honoree_email'), 'trim|required|valid_email');
			if ($sendTo == 'recipient'){
				$ci->form_validation->set_rules('recipientName', lang_line('label_receipeint_name'), 'trim|required');

				$recipientName = $ci->input->post("recipientName");
				$ci->form_validation->set_rules('recipientEmail', lang_line('label_receipeint_email'), 'trim|required|valid_email');
			}
			
			if ( $sendType == 'schedule' ){
				$ci->form_validation->set_rules('schedule_ecard', lang_line('text_schedule_date'), 'trim|required');

				$schedule_date = $ci->input->post("schedule_ecard");
			}
			$ci->form_validation->set_rules('donating_amount', lang_line('label_donate_amount'), 'trim|required|numeric');
			$ci->form_validation->set_rules('name', lang_line('label_your_name'), 'trim|required|callback_validate_name');
			$ci->form_validation->set_rules('card_email', lang_line('text_email'), 'trim|required|valid_email');
			$ci->form_validation->set_rules('honor_home_country', lang_line('text_country'), 'required');

			if( site_url() == "https://imicanada.org/"){
				$ci->form_validation->set_rules('donate_address', 'Address', 'trim|required');
			}
			// $ci->form_validation->set_rules('honor_home_state', lang_line('text_state'), 'required');
			// $ci->form_validation->set_rules('honor_home_city', lang_line('text_city'), 'required');
			// $ci->form_validation->set_rules('custom_grecap', lang_line('text_captcha'), 'trim|required|callback_validate_recaptcha');
			
			$card_information="";
			if ($ci->input->post('honor_paymenttype') == "card") {
				$ci->form_validation->set_rules('honor_paymenttype', lang_line('text_paymenttype'), 'trim|required');
				$ci->form_validation->set_rules('honor_card_name', lang_line('label_card_holder'), 'trim|required');
				$ci->form_validation->set_rules('honor_card_number', lang_line('label_card_no'), 'trim|required');
				$ci->form_validation->set_rules('honor_card_expiry', lang_line('label_card_expiry'), 'trim|required');
				$ci->form_validation->set_rules('honor_card_cvv', lang_line('label_card_cvv'), 'trim|required');

				$card_information = array(
					'type' => $ci->input->post("honor_paymenttype"),
					'name' => $ci->input->post("honor_card_name"),
					'number' => str_replace(' ', '', $ci->input->post("honor_card_number")),
					'expiry' => $ci->input->post("honor_card_expiry"),
					'cvv' => $ci->input->post("honor_card_cvv")
				);
			}
			
			if ($ci->form_validation->run() == FALSE)
			{
				//die("ssssff");
				$_POST['errorHonor'] = TRUE;

				$ci->form_validation->set_error_delimiters('<p class="form_error">', '</p>');

				return TRUE;

			} else {
				
				$tmp_countries			= $ci->imiconf_queries->fetch_records_imiconf("countries", " AND id = " . $ci->input->post("honor_home_country"))->result()[0];
				$Country = $tmp_countries->countries_iso_code_2;
				//die("sssss");

				if($ci->input->post("honor_paymenttype")=="paypal"){

					$data[ $data['__UNIQUE'] ]['_process_to_paypal']								= TRUE;
					$data[ $data['__UNIQUE'] ]['_business_email']									= $ci->input->post("active_paypal");
					$data[ $data['__UNIQUE'] ]['currency_code']										= $ci->input->post("currency_code");
				} 
				else if ($ci->input->post("honor_paymenttype") == "card") {
					//Pay via Payeezy
					$rand_uuid = $ci->functions->gen_uuid();
					$pay       = new Payeezy();
					//$recurring = $ci->input->post('donation_mode') == 'recurring' ? true : false;
					//$pay = $pay->pay($card_information, $_POST['donate_amount'], $recurring);
					$pay = $pay->pay($card_information, $_POST['donating_amount'], $_POST['card_email'], false, $rand_uuid, 'tb_give_honor_someone');
					// /* var_dump($pay);  */die('syhgtsduasdf');
					// $this->load->library('custom_log');
					if (isset($pay['error'])) {
						$logging = new Custom_log;
						$logging->write_log('error',  __FILE__.' '.$pay['error']. " line " .__LINE__.'  '.print_r($pay['request'],true));	
		     		}	
					// $this->custom_log->error($pay['error']);
					// $this->custom_log->info($pay['error']);	



					//if (is_array($pay) && isset($pay['error'])) {
					if ( ! is_array($pay) ){
						// $data['_messageBundle']				= $ci->_messageBundle(
						// 	'danger',
						// 	$pay['error'],
						// 	'Error'
						// );

						//$data['_pageview']						= "global/_blank_page.php";

						//unset($_POST);

						//return true;

						//$data['card_error'] = $pay['error'];

						$data['card_error'] = "Something Went Wrong! Please Try Again.";
						$logging = new Custom_log;
						$logging->write_log('error',  __FILE__.' '.$pay['error']. " line " .__LINE__);
					
						// $this->load->library('log');
						// $log_filename = 'payeezy_error_log';
						// $this->log->set_log_filename($log_filename);
						// $error_msg = "Payeezy Card Payment Error.";
					 //     log_message('error', $error_msg);
						return TRUE;
					}
				}

				$insertData					= array(
													"name"			=> $ci->input->post("honorto"),
													"fromname"		=> $ci->input->post("fromname"),
													"recipientname"	=> $recipientName,
													"email"			=> $ci->input->post("recipientEmail"),
													"honoree_email"	=> $ci->input->post("honoreeEmail"),
													"message"		=> $ci->input->post("message"),
													"send_to"		=> $sendTo,
													"send_type"		=> $sendType,
													"schedule_date"	=> $schedule_date,
													"donate_amount"	=> $ci->input->post("donating_amount"),
													"card_name"		=> $ci->input->post("name"),
													"card_email"	=> $ci->input->post("card_email"),
													"home_country"	=> $ci->input->post("honor_home_country"),
													"home_city"		=> $ci->input->post("honor_home_city"),
													"home_state"	=> $ci->input->post("honor_home_state"),
													"belongs_country"	=> $ci->input->post("belongs_country"),
													"user_id"		=> $ci->functions->_user_logged_in_details( "id" ),
													"date_added"	=> date("Y-m-d H:i:s"),
													"payeezy_uuid"  => $rand_uuid
												);
												$insertData['belongs_country'] = $ci->input->post('belongs_country');
				/* if($ci->input->post('home_country') == 38){
					$insertData['belongs_country'] = 3;
				}else{
					$insertData['belongs_country'] = 2;
				} */

				$ci->queries->SaveDeleteTables($insertData, 's', "tb_give_honor_someone", 'id');

				$data['donation_id']	= $ci->db->insert_id();
				$donationId				= $ci->db->insert_id();

				if($ci->input->post("honor_paymenttype")=="paypal"){
					return TRUE;
				} elseif ($ci->input->post( "honor_paymenttype") == "paypal_pro") {

					$address=$ci->input->post("address");

					$DPFields = array(
							'paymentaction' => 'Sale', 						// How you want to obtain payment.  Authorization indidicates the payment is a basic auth subject to settlement with Auth & Capture.  Sale indicates that this is a final sale for which you are requesting payment.  Default is Sale.
							'ipaddress' => $_SERVER['REMOTE_ADDR'], 							// Required.  IP address of the payer's browser.
							'returnfmfdetails' => '1' 					// Flag to determine whether you want the results returned by FMF.  1 or 0.  Default is 0.
							);

					$CCDetails = array(
									'creditcardtype' => $card_information['paymenttype'], 					// Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
									'acct' => $card_information['card_number'], 								// Required.  Credit card number.  No spaces or punctuation.  
									'expdate' => $card_information['expiration'], 							// Required.  Credit card expiration date.  Format is MMYYYY
									'cvv2' => $card_information['ccv'], 								// Requirements determined by your PayPal account settings.  Security digits for credit card.
									'startdate' => '', 							// Month and year that Maestro or Solo card was issued.  MMYYYY
									'issuenumber' => ''							// Issue number of Maestro or Solo card.  Two numeric digits max.
									);


					
					$PayerInfo = array(
									'email' => $insertData['card_email'], 								// Email address of payer.
									'payerid' => '', 							// Unique PayPal customer ID for payer.
									'payerstatus' => '', 						// Status of payer.  Values are verified or unverified
									'business' => 'Donation, LLC' 							// Payer's business name.
									);

					$PayerName = array(
									'salutation' => 'Mr\Mrs', 						// Payer's salutation.  20 char max.
									'firstname' => $insertData['card_name'], 							// Payer's first name.  25 char max.
									'middlename' => '', 						// Payer's middle name.  25 char max.
									'lastname' => '', 							// Payer's last name.  25 char max.
									'suffix' => ''								// Payer's suffix.  12 char max.
									);



					$BillingAddress = array(
										'street' =>'', 						// Required.  First street address.
										'street2' => '', 						// Second street address.
										'city' => '', 							// Required.  Name of City.
										'state' => '', 							// Required. Name of State or Province.
										'countrycode' =>$Country, 					// Required.  Country code.
										'zip' => '', 							// Required.  Postal code of payer.
										'phonenum' =>'' 						// Phone Number of payer.  20 char max.
										);

					$ShippingAddress = array(
										'shiptoname' => $insertData['card_name'], 					// Required if shipping is included.  Person's name associated with this address.  32 char max.
										'shiptostreet' =>'', 					// Required if shipping is included.  First street address.  100 char max.
										'shiptostreet2' => '', 					// Second street address.  100 char max.
										'shiptocity' => '', 					// Required if shipping is included.  Name of city.  40 char max.
										'shiptostate' =>'', 					// Required if shipping is included.  Name of state or province.  40 char max.
										'shiptozip' => '', 						// Required if shipping is included.  Postal code of shipping address.  20 char max.
										'shiptocountry' =>$Country, 					// Required if shipping is included.  Country code of shipping address.  2 char max.
										'shiptophonenum' =>''					// Phone number for shipping address.  20 char max.
										);

					$PaymentDetails = array(
										'amt' => $insertData['donate_amount'], 							// Required.  Total amount of order, including shipping, handling, and tax.  
										'currencycode' => 'USD', 					// Required.  Three-letter currency code.  Default is USD.
										'itemamt' => $insertData['donate_amount'], 						// Required if you include itemized cart details. (L_AMTn, etc.)  Subtotal of items not including S&H, or tax.
										'shippingamt' => '', 					// Total shipping costs for the order.  If you specify shippingamt, you must also specify itemamt.
										'shipdiscamt' => '', 					// Shipping discount for the order, specified as a negative number.  
										'handlingamt' => '', 					// Total handling costs for the order.  If you specify handlingamt, you must also specify itemamt.
										'taxamt' => '', 						// Required if you specify itemized cart tax details. Sum of tax for all items on the order.  Total sales tax. 
										'desc' => 'Web Order', 							// Description of the order the customer is purchasing.  127 char max.
										'custom' => '', 						// Free-form field for your own use.  256 char max.
										'invnum' => '', 						// Your own invoice or tracking number
										'notifyurl' => ''						// URL for receiving Instant Payment Notifications.  This overrides what your profile is set to use.
										);	

					$OrderItems = array();
					$Item	 = array(
									'l_name' => $insertData['donate_type'], 						// Item Name.  127 char max.
									'l_desc' => 'The best test widget on the planet!', 						// Item description.  127 char max.
									'l_amt' => $insertData['donate_amount'], 							// Cost of individual item.
									'l_number' => '', 						// Item Number.  127 char max.
									'l_qty' => '1', 							// Item quantity.  Must be any positive integer.  
									'l_taxamt' => '', 						// Item's sales tax amount.
									'l_ebayitemnumber' => '', 				// eBay auction number of item.
									'l_ebayitemauctiontxnid' => '', 		// eBay transaction ID of purchased item.
									'l_ebayitemorderid' => '' 				// eBay order ID for the item.
									);


					array_push($OrderItems, $Item);

					$Secure3D = array(
						'authstatus3d' => '', 
						'mpivendor3ds' => '', 
						'cavv' => '', 
						'eci3ds' => '', 
						'xid' => ''
						);

					$PayPalRequestData = array(
						'DPFields' => $DPFields, 
						'CCDetails' => $CCDetails, 
						'PayerInfo' => $PayerInfo, 
						'PayerName' => $PayerName, 
						'BillingAddress' => $BillingAddress, 
						'ShippingAddress' => $ShippingAddress, 
						'PaymentDetails' => $PaymentDetails, 
						'OrderItems' => $OrderItems, 
						'Secure3D' => $Secure3D
						);
					
					$PayPalResult = $ci->paypal_pro->DoDirectPayment($PayPalRequestData);

					if(!$ci->paypal_pro->APICallSuccessful($PayPalResult['ACK']))
					{
						$error="";
						$error=$PayPalResult['L_LONGMESSAGE0'];

						$data['_messageBundle']					= $ci->_messageBundle( 	'danger_big' , 
							$error);


			 		  //$data['_messageBundle']					= $ci->_messageBundle( 	'danger_big' , $error);

						$data['_pageview']						= "global/_blank_page.php";

						unset($_POST);		


						return true;
					}
					else
					{

						if($PayPalResult['ACK']=="SuccessWithWarning"){

							$editData=array();     
							$editData['is_paid']=1;
							$editData['id']=$donationId;

							$ci->queries->SaveDeleteTables($editData, 'e', "tb_give_honor_someone", 'id'); 			

							$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like");

							$email_template				= array(
								"email_to" => 			$ci->input->post('recipientEmail'),
								"email_heading"			=> lang_line('honor_email', array($ci->input->post("honorto"))),
								"email_file"			=> "email/frontend/give_honor.php",
								"email_subject"			=> lang_line('honor_email', array($ci->input->post("honorto"))),
								"email_post"			=> $_POST
							);

							$is_email_sent				= $ci->_send_email( $email_template );

							$data['_pageview']						= "global/_blank_page.php";	

							unset($_POST);	
							return true; 
						}

						if($PayPalResult['ACK']=="Success"){

							$editData=array();     

							$editData['is_paid']=1;
							$editData['id']=$donationId;

							$ci->queries->SaveDeleteTables($editData, 'e', "tb_give_honor_someone", 'id'); 			


							$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like");

							$email_template				= array(
								"email_to" => 			$ci->input->post('recipientEmail'),
								"email_heading"			=> lang_line('honor_email', array($ci->input->post("honorto"))),
								"email_file"			=> "email/frontend/give_honor.php",
								"email_subject"			=> lang_line('honor_email', array($ci->input->post("honorto"))),
								"email_post"			=> $_POST
							);

							$is_email_sent				= $ci->_send_email( $email_template );

							$data['_pageview']						= "global/_blank_page.php";	

							unset($_POST);	

							return true;
						}
					}
				} elseif ($ci->input->post( "honor_paymenttype") == "card"){

					$TMP_table	= $ci->db->query("SELECT * FROM tb_give_honor_someone WHERE id = '" . $donationId . "' ");

					$editData = array();
					if ( isset($pay['success'] ) ){
						$editData['is_paid'] 	= 1;
						$editData['id'] 		= $donationId;
						$ci->queries->SaveDeleteTables($editData, 'e', "tb_give_honor_someone", 'id');
					}

					$saveData		= array(
						"user_id"		=> $ci->functions->_user_logged_in_details("id"),
						"payer_email"	=> $ci->input->post("card_email"),
						"payment_status"=> isset($pay['success'] ) ? 'Completed' : 'Failed',
						"payment_mode"	=> 'payeezy',
						"table_name"	=> 'tb_give_honor_someone',
						"table_id_name"	=> 'id',
						"table_id_value"=> $donationId,
						"date_added"	=> date("Y-m-d H:i:s")
					);

					$ci->queries->SaveDeleteTables($saveData, 's', "tb_donation_payments", 'id');
					$payment_id = $ci->db->insert_id();

					$saveData		= array(
						"payment_id"		=> $payment_id,
						"card_name"			=> $card_information['name'],
						'card_type'			=> $card_information['type'],
						'card_expiry'		=> $card_information['expiry'],
						'ref_no'			=> isset($pay['response']->Retrieval_Ref_No ) ? $pay['response']->Retrieval_Ref_No : '',
						'amount'			=> isset($pay['response']->DollarAmount ) ? $pay['response']->DollarAmount : '',
						'currency'			=> isset($pay['response']->Currency ) ? $pay['response']->Currency : '',
						'transaction_tag'	=> isset($pay['response']->Transaction_Tag ) ? $pay['response']->Transaction_Tag : '',
						'ctr'				=> isset($pay['response']->CTR ) ? $pay['response']->CTR : '',
						'transaction_id'	=> isset($pay['response']->Authorization_Num ) ? $pay['response']->Authorization_Num : '',
						'sequence_no'		=> isset($pay['response']->SequenceNo ) ? $pay['response']->SequenceNo : '',
						"date_added"		=> date("Y-m-d H:i:s"),
					);

					if(isset($pay['response']->CTR)){
						unset($pay['response']->CTR);
					}
					unset($pay['request']['Card_Number']);
					$saveData['payeezy_post'] = isset($pay['response'] ) ? json_encode($pay['response']) : '';
					$saveData['request_data'] = isset($pay['request'] ) ? json_encode($pay['request']) : '';

					$ci->queries->SaveDeleteTables($saveData, 's', "tb_card_payments", 'id');

					if ( isset($pay['error'] ) ){
						$data['card_error'] = $pay['error'];
						return TRUE;
					}

					#*************************** *************************** *************************** 
					#*************************** 	    EMAIL PREPARATION 	 *************************** 
					#*************************** *************************** *************************** 


					$toname  = $TMP_table->row()->send_to == 'honoree' ? $TMP_table->row()->name : $TMP_table->row()->recipientname;
					$toemail = $TMP_table->row()->send_to == 'honoree' ? $TMP_table->row()->honoree_email : $TMP_table->row()->email;
					
					$email_template				= array("email_to"=>$toemail,
										"email_heading"			=> "Your gift from ".$TMP_table->row()->fromname ,
										"donating_amount"		=> $TMP_table->row()->donate_amount,
										"toname"				=> $toname,
										"fromname"				=> $TMP_table->row()->fromname,
										"emailtosender"			=> FALSE,
										"message"				=> $TMP_table->row()->message,
										"email_file"			=> "email/frontend/give_honor.php",
										"email_subject"			=> "Your gift from ".$TMP_table->row()->fromname,
										"default_subject"		=> TRUE,
									);

					$is_email_sent				= $ci->_send_email( $email_template );



					$email_template2				= array("email_to"=>$TMP_table->row()->card_email,
										"email_heading"			=> "Gift sent to ". $toname,
										"donating_amount"		=> $TMP_table->row()->donate_amount,
										"toname"				=> $toname,
										"fromname"				=> $TMP_table->row()->fromname,
										"emailtosender"			=> TRUE,
										"message"				=> $TMP_table->row()->message,
										"email_file"			=> "email/frontend/give_honor.php",
										"email_subject"			=> "Gift sent to ". $toname,
										"default_subject"		=> TRUE,
									);

					$is_email_sent				= $ci->_send_email( $email_template2 );

					$ci->functions->send_tax_receipt_honor($donationId, $ci);
					
					#*************************** *************************** *************************** 
					#*************************** 	    EMAIL PREPARATION 	 *************************** 
					#*************************** *************************** *************************** 
					$data['_messageBundle']					= $ci->_messageBundle(
						'success_big',
						"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612",
						"You’re what making a difference looks like"
					);

					$data['_pageview']						= "global/_blank_page.php";
					$data['is_donation_success']			= "yes";

					unset($_POST);

					return true;
				}
			}

			
			
			/*$data['_messageBundle']		= $ci->_messageBundle( 	'success_big' , 
				"Thank you for email.","");

			$data['_pageview']						= "global/_blank_page.php";	*/
			//unset($_POST);

			//redirect(base_url(uri_string().'#payForHonor'));
			return true;
		}
		//If Give In Honor Of Someone
		

		$ci->form_validation->set_rules('donation_projects', lang_line('text_donationprojects'), 'trim|required');
		$ci->form_validation->set_rules('donate_amount', lang_line('label_donate_amount'), 'trim|required|numeric');
		$ci->form_validation->set_rules('custom_amount', "Custom Donation", 'trim|numeric');
		$ci->form_validation->set_rules('donate_name', lang_line('text_name'), 'required|callback_validate_name');
		$ci->form_validation->set_rules('donate_email', lang_line('text_email'), 'trim|required|valid_email');	
		if( site_url() == "https://imicanada.org/"){
			$ci->form_validation->set_rules('donate_address', 'Address', 'trim|required');	
		}	
		$ci->form_validation->set_rules('donation_mode', lang_line('text_donatemode'), 'trim|required');
		$ci->form_validation->set_rules('home_country', lang_line('text_country'), 'required');
		// $ci->form_validation->set_rules('home_state', lang_line('text_state'), 'required');
		// $ci->form_validation->set_rules('home_city', lang_line('text_city'), 'required');
		// $ci->form_validation->set_rules('custom_grecap', lang_line('text_captcha'), 'trim|required|callback_validate_recaptcha');
		
		if (  $ci->input->post('isEmpMatch') == '1' ){
			$ci->form_validation->set_rules('donation_empnames', lang_line('text_employeename'), 'trim|required');
			$ci->form_validation->set_rules('donate_empemail', lang_line('text_employeeemail'), 'trim|required|valid_email');
		}

		if ( $ci->input->post("donation_mode") == "recurring" )
		{
			$ci->form_validation->set_rules('donation_freq', lang_line('text_donatefrquency'), 'trim|required');
		}
		
		$card_information="";
		
		if($ci->input->post('paymenttype')=="card"){	
			$ci->form_validation->set_rules('paymenttype', lang_line('text_paymenttype'), 'trim|required');
			$ci->form_validation->set_rules('card_name', lang_line('label_card_holder'), 'trim|required');
			$ci->form_validation->set_rules('card_number', lang_line('label_card_no'), 'trim|required');
			$ci->form_validation->set_rules('card_expiry', lang_line('label_card_expiry'), 'trim|required');
			$ci->form_validation->set_rules('card_cvv', lang_line('label_card_cvv'), 'trim|required');

			$card_number = str_replace(' ','',$ci->input->post("card_number"));
			$card_information=array(
				'type'=> $ci->ccdetector->detect($card_number),
				'name'=>$ci->input->post("card_name"),
				'number'=> $card_number,
				'expiry'=>$ci->input->post("card_expiry"),
				'cvv'=>$ci->input->post("card_cvv")
			);
		}
		
		if( !empty($ci->input->post("custom_amount")) ){
			$_POST['donate_amount']	= $ci->input->post("custom_amount");
		}

		if ($ci->form_validation->run() == FALSE)
		{
			$ci->form_validation->set_error_delimiters('<p class="form_error">', '</p>');
			return TRUE;
		}
		else 
		{	
			$tmp_countries			= $ci->imiconf_queries->fetch_records_imiconf("countries", " AND id = " . $ci->input->post("home_country"))->result()[0];
			$Country = $tmp_countries->countries_iso_code_2;

			if($ci->input->post("paymenttype")=="paypal"){
			  
			  	$data[ $data['__UNIQUE'] ]['_process_to_paypal']								= TRUE;
				$data[ $data['__UNIQUE'] ]['_business_email']									= $ci->input->post("active_paypal");
				$data[ $data['__UNIQUE'] ]['currency_code']										= $ci->input->post("currency_code");

			}else if ( $ci->input->post("paymenttype")=="card" ){
				//Pay via Payeezy
				$rand_uuid = $ci->functions->gen_uuid();
				$pay = new Payeezy();
				$recurring = $ci->input->post('donation_mode') == 'recurring' ? true : false;
				//$pay = $pay->pay($card_information, $_POST['donate_amount'],$recurring);
				// var_dump($rand_uuid);
				$pay = $pay->pay($card_information, $_POST['donate_amount'], $_POST['donate_email'], $recurring,$rand_uuid);
				
				// echo "<pre>";	
				// var_dump($pay['request']);	


				if (isset($pay['error'])) {
					$logging = new Custom_log;
				
					$logging->write_log('error',  __FILE__.' '.$pay['error']. " line " .__LINE__.'  '.print_r($pay['request'],true));	
		     	}		
				if ( ! is_array($pay) ){
					$data['card_error'] = "Something Went Wrong! Please Try Again.";
					return TRUE;
				}
				/*if ( isset($pay['error'] ) ){
					// $data['_messageBundle']				= $ci->_messageBundle(
					// 	'danger',
					// 	$pay['error'],
					// 	'Error'
					// );

					//$data['_pageview']						= "global/_blank_page.php";

					//unset($_POST);

					//return true;

					$data['card_error'] = $pay['error'];
					return TRUE;
				}*/
			}
			if ( $ci->input->post("donation_mode") == "recurring" )
			{
				$data[ $data['__UNIQUE'] ]['_is_recurring']									= TRUE;
			}

			$checkIfaCampg = DropdownHelper::donation_projects_dropdown(false, $ci->input->post("donation_projects"), true);
			$selected_project	= $ci->db->query("SELECT * FROM tb_donation_projects WHERE id = " . $ci->input->post('donation_projects'))->row();

			$insertData	= array(
				"donation_projects_id"	=> $ci->input->post("donation_projects"	),
				"first_name"			=> $ci->input->post("donate_name"),
				"email"				 	=> $ci->input->post("donate_email"),
				"donation_mode"			=> $ci->input->post("donation_mode"),
				"donation_freq"			=> $ci->input->post("donation_freq"),
				"donate_amount"			=> $ci->input->post("donate_amount"),
				"emp_name"				=> $ci->input->post("donation_empnames"),
				"emp_email"				=> $ci->input->post("donate_empemail"),
				"home_country"			=> $ci->input->post("home_country"),
				"home_city"				=> $ci->input->post("home_city"),
				"home_state"			=> $ci->input->post("home_state"),
				"home_address"			=> $ci->input->post("donate_address"),
				"home_zipcode"			=> $ci->input->post("home_zipcode"),
				"belongs_country"		=> $ci->input->post("belongs_country"),
				"user_id"				=> $ci->functions->_user_logged_in_details( "id" ),
				"date_added"			=> date("Y-m-d H:i:s"),
				"type"					=> "imiportal",
				"payeezy_uuid"			=> $rand_uuid
			);
			if(!is_null($checkIfaCampg) && isset($_POST["hideIdentity"])){
				$insertData['hide_identity']			= 1;
			}
			if($selected_project->type == 'Khums'){	
				// $insertData['sehm'] = $ci->input->post('sehm');	
				// $insertData['marjaa'] = $ci->input->post('marjaa_taqleed') ? $ci->input->post('marjaa_taqleed') : null;
			}	
			if($selected_project->type == 'Fitrana'){	
				// $insertData['is_syed'] = $ci->input->post('is_syed') ? ($ci->input->post('is_syed') == 'Syed' ? 'yes' : 'no') : 'no';
			}
			$insertData['belongs_country'] = $ci->input->post('belongs_country');
			/* if($ci->input->post('home_country') == 38){
				$insertData['belongs_country'] = 3;
			}else{
				$insertData['belongs_country'] = 2;
			} */
			$ci->queries->SaveDeleteTables($insertData, 's', "tb_donation_form", 'id'); 		
			$data['donation_id']		= $ci->db->insert_id();
			$donationId=$ci->db->insert_id();
			
			if(!is_null($checkIfaCampg) && trim($ci->input->post("comment") != "")){
				$insertDataComment	= array(
					"df_id"					=> $donationId,
					"comment"				=> $ci->input->post("comment"),
					"status"				=> 0,
					"added_date"			=> date("Y-m-d H:i:s"),
				);
				$ci->queries->SaveDeleteTables($insertDataComment, 's', "tb_dc_comments", 'id');
			}

			$_comment_proj = ($ci->input->post('dp_donor_comment')) ? $ci->input->post('dp_donor_comment') : false;
			if($_comment_proj && trim($ci->input->post("dp_donor_comment") != "")){
				$insertDataComment	= array(
					"df_id"					=> $donationId,
					"dp_id"					=> $ci->input->post("donation_projects"),
					"comment"				=> $ci->input->post("dp_donor_comment"),
					"added_date"			=> date("Y-m-d H:i:s"),
				);
				$ci->queries->SaveDeleteTables($insertDataComment, 's', "tb_df_dp_comments", 'id');
			}
			if($ci->input->post("donate_type")=="gift_of_securities"){

				$data['_messageBundle']					= $ci->_messageBundle( 	'success_big' , 
																				"Thank you for donating us.","");
			
				$data['_pageview']						= "global/_blank_page.php";	

					unset($_POST);	
					return true;
			}else{
	
				if($ci->input->post("paymenttype")=="paypal"){
					return TRUE;
			  	}elseif( $ci->input->post("paymenttype")=="paypal_pro" ){
					$address=$ci->input->post("address");
			
					$DPFields = array(
						'paymentaction' => 'Sale', 						// How you want to obtain payment.  Authorization indidicates the payment is a basic auth subject to settlement with Auth & Capture.  Sale indicates that this is a final sale for which you are requesting payment.  Default is Sale.
						'ipaddress' => $_SERVER['REMOTE_ADDR'], 							// Required.  IP address of the payer's browser.
						'returnfmfdetails' => '1' 					// Flag to determine whether you want the results returned by FMF.  1 or 0.  Default is 0.
					);
					
					$CCDetails = array(
						'creditcardtype' => $card_information['paymenttype'], 					// Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
						'acct' => $card_information['card_number'], 								// Required.  Credit card number.  No spaces or punctuation.  
						'expdate' => $card_information['expiration'], 							// Required.  Credit card expiration date.  Format is MMYYYY
						'cvv2' => $card_information['ccv'], 								// Requirements determined by your PayPal account settings.  Security digits for credit card.
						'startdate' => '', 							// Month and year that Maestro or Solo card was issued.  MMYYYY
						'issuenumber' => ''							// Issue number of Maestro or Solo card.  Two numeric digits max.
					);

					$PayerInfo = array(
						'email' => $insertData['email'], 								// Email address of payer.
						'payerid' => '', 							// Unique PayPal customer ID for payer.
						'payerstatus' => '', 						// Status of payer.  Values are verified or unverified
						'business' => 'Donation, LLC' 							// Payer's business name.
					);
						
					$PayerName = array(
						'salutation' => 'Mr\Mrs', 						// Payer's salutation.  20 char max.
						'firstname' => $insertData['first_name'], 							// Payer's first name.  25 char max.
						'middlename' => '', 						// Payer's middle name.  25 char max.
						'lastname' => '', 							// Payer's last name.  25 char max.
						'suffix' => ''								// Payer's suffix.  12 char max.
					);

					$BillingAddress = array(
						'street' =>'', 						// Required.  First street address.
						'street2' => '', 						// Second street address.
						'city' => '', 							// Required.  Name of City.
						'state' => '', 							// Required. Name of State or Province.
						'countrycode' =>$Country, 					// Required.  Country code.
						'zip' => '', 							// Required.  Postal code of payer.
						'phonenum' =>'' 						// Phone Number of payer.  20 char max.
					);
							
					$ShippingAddress = array(
						'shiptoname' => $insertData['first_name'], 					// Required if shipping is included.  Person's name associated with this address.  32 char max.
						'shiptostreet' =>'', 					// Required if shipping is included.  First street address.  100 char max.
						'shiptostreet2' => '', 					// Second street address.  100 char max.
						'shiptocity' => '', 					// Required if shipping is included.  Name of city.  40 char max.
						'shiptostate' =>'', 					// Required if shipping is included.  Name of state or province.  40 char max.
						'shiptozip' => '', 						// Required if shipping is included.  Postal code of shipping address.  20 char max.
						'shiptocountry' =>$Country, 					// Required if shipping is included.  Country code of shipping address.  2 char max.
						'shiptophonenum' =>''					// Phone number for shipping address.  20 char max.
					);

					$PaymentDetails = array(
						'amt' => $insertData['donate_amount'], 							// Required.  Total amount of order, including shipping, handling, and tax.  
						'currencycode' => 'USD', 					// Required.  Three-letter currency code.  Default is USD.
						'itemamt' => $insertData['donate_amount'], 						// Required if you include itemized cart details. (L_AMTn, etc.)  Subtotal of items not including S&H, or tax.
						'shippingamt' => '', 					// Total shipping costs for the order.  If you specify shippingamt, you must also specify itemamt.
						'shipdiscamt' => '', 					// Shipping discount for the order, specified as a negative number.  
						'handlingamt' => '', 					// Total handling costs for the order.  If you specify handlingamt, you must also specify itemamt.
						'taxamt' => '', 						// Required if you specify itemized cart tax details. Sum of tax for all items on the order.  Total sales tax. 
						'desc' => 'Web Order', 							// Description of the order the customer is purchasing.  127 char max.
						'custom' => '', 						// Free-form field for your own use.  256 char max.
						'invnum' => '', 						// Your own invoice or tracking number
						'notifyurl' => ''						// URL for receiving Instant Payment Notifications.  This overrides what your profile is set to use.
					);
					
					$OrderItems = array();
					$Item	 = array(
						'l_name' => $insertData['donate_type'], 						// Item Name.  127 char max.
						'l_desc' => 'The best test widget on the planet!', 						// Item description.  127 char max.
						'l_amt' => $insertData['donate_amount'], 							// Cost of individual item.
						'l_number' => '', 						// Item Number.  127 char max.
						'l_qty' => '1', 							// Item quantity.  Must be any positive integer.  
						'l_taxamt' => '', 						// Item's sales tax amount.
						'l_ebayitemnumber' => '', 				// eBay auction number of item.
						'l_ebayitemauctiontxnid' => '', 		// eBay transaction ID of purchased item.
						'l_ebayitemorderid' => '' 				// eBay order ID for the item.
					);
					
					array_push($OrderItems, $Item);
			
					$Secure3D = array(
						'authstatus3d' => '', 
						'mpivendor3ds' => '', 
						'cavv' => '', 
						'eci3ds' => '', 
						'xid' => ''
					);
	
					$PayPalRequestData = array(
						'DPFields' => $DPFields, 
						'CCDetails' => $CCDetails, 
						'PayerInfo' => $PayerInfo, 
						'PayerName' => $PayerName, 
						'BillingAddress' => $BillingAddress, 
						'ShippingAddress' => $ShippingAddress, 
						'PaymentDetails' => $PaymentDetails, 
						'OrderItems' => $OrderItems, 
						'Secure3D' => $Secure3D
					);
				
					$PayPalResult = $ci->paypal_pro->DoDirectPayment($PayPalRequestData);

					if(!$ci->paypal_pro->APICallSuccessful($PayPalResult['ACK']))
					{
						$error="";
						$error=$PayPalResult['L_LONGMESSAGE0'];

						$data['_messageBundle']					= $ci->_messageBundle( 	'danger_big' , $error);
			
						$data['_pageview']						= "global/_blank_page.php";
			
						unset($_POST);
						return true;
					}
					else
					{
						if($PayPalResult['ACK']=="SuccessWithWarning"){
							$editData=array();     
							$editData['is_paid']=1;
							$editData['id']=$donationId;

							$ci->queries->SaveDeleteTables($editData, 'e', "tb_donation_form", 'id'); 			
					
							$data['_messageBundle']				= $ci->_messageBundle( 
								'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like"
							);
				
							$data['_pageview']						= "global/_blank_page.php";	
								
							unset($_POST);	
							return true; 
						}

						if($PayPalResult['ACK']=="Success"){
							$editData=array();     

							$editData['is_paid']=1;
							$editData['id']=$donationId;
						
							$ci->queries->SaveDeleteTables($editData, 'e', "tb_donation_form", 'id'); 			
			

							$data['_messageBundle']				= $ci->_messageBundle(
								'success_big' , 
								"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612", 
								"You’re what making a difference looks like"
							);
				
							$data['_pageview']						= "global/_blank_page.php";	

							unset($_POST);	

							return true;
						}
					}
		    	}elseif( $ci->input->post("paymenttype")=="card" ){

					//$TMP_table	= $ci->db->query("SELECT * FROM tb_donation_form WHERE id = '" . $donationId . "' ");
					$TMP_table	= $ci->db->query("SELECT df.*, dcc.comment as comments FROM tb_donation_form df LEFT JOIN `tb_dc_comments` dcc ON df.id = dcc.df_id WHERE df.id = '". $donationId ."' ");

					$editData = array();
					if ( isset($pay['success'] ) ){
						$editData['is_paid'] = 1;
						$editData['id'] = $donationId;
						$ci->queries->SaveDeleteTables($editData, 'e', "tb_donation_form", 'id');
					}

					$saveData		= array(
						"user_id"		=> $ci->functions->_user_logged_in_details("id"),
						"payer_email"	=> $ci->input->post("donate_email"),
						"payment_status"=> isset($pay['success'] ) ? 'Completed' : 'Failed',
						"payment_mode"	=> 'payeezy',
						"table_name"	=> 'tb_donation_form',
						"table_id_name"	=> 'id',
						"table_id_value"=> $donationId,
						"date_added"	=> date("Y-m-d H:i:s")
					);

					$ci->queries->SaveDeleteTables($saveData, 's', "tb_donation_payments", 'id');
					$payment_id = $ci->db->insert_id();
					
					$saveData		= array(
						"payment_id"		=> $payment_id,
						"card_name"			=> $card_information['name'],
						'card_type'			=> isset($pay['response']->CardType ) ? $pay['response']->CardType : $card_information['type'],
						'card_expiry'		=> $card_information['expiry'],
						'ref_no'			=> isset($pay['response']->Retrieval_Ref_No ) ? $pay['response']->Retrieval_Ref_No : '',
						'amount'			=> isset($pay['response']->DollarAmount ) ? $pay['response']->DollarAmount : '',
						'currency'			=> isset($pay['response']->Currency ) ? $pay['response']->Currency : '',
						'transaction_tag'	=> isset($pay['response']->Transaction_Tag ) ? $pay['response']->Transaction_Tag : '',
						'ctr'				=> isset($pay['response']->CTR ) ? $pay['response']->CTR : '',
						'transaction_id'	=> isset($pay['response']->Authorization_Num ) ? $pay['response']->Authorization_Num : '',
						'sequence_no'		=> isset($pay['response']->SequenceNo ) ? $pay['response']->SequenceNo : '',
						"date_added"		=> date("Y-m-d H:i:s"),
					);

					if(isset($pay['response']->CTR)){
						unset($pay['response']->CTR);
					}
					if(isset($pay['request']['Card_Number'])){
						unset($pay['request']['Card_Number']);
					}
					$saveData['payeezy_post'] 		= isset($pay['response'] ) ? json_encode($pay['response']) : '';
					$saveData['request_data'] 		= isset($pay['request'] ) ? json_encode($pay['request']) : '';
					if($recurring){
						$saveData['token'] 			= isset($pay['response']->TransarmorToken ) ? $pay['response']->TransarmorToken : '';
						$saveData['trans_recur_id'] = isset($pay['response']->StoredCredentials->TransactionId ) ? $pay['response']->StoredCredentials->TransactionId : '';
					}


					$ci->queries->SaveDeleteTables($saveData, 's', "tb_card_payments", 'id');

					if ( isset($pay['error'] ) ){
						$data['card_error'] = $pay['error'];
						return TRUE;
					}
					#*************************** *************************** *************************** 
					#*************************** 	    EMAIL PREPARATION 	 *************************** 
					#*************************** *************************** *************************** 
			
					#to_user
					$_POST["TEXT_p"]				= 'Dear ' . $TMP_table->row()->first_name . ' ' . $TMP_table->row()->last_name . ',
													<br /> <br />Thank you for donating <br><br>
													Donate Date: ' . date("d-m-Y");
													
					$_POST["donation_details"]		= $TMP_table->row_array();
												
											
					$email_template					= array("email_to"				=> $TMP_table->row()->email,
														"email_heading"				=> "Donation Form",
														"email_file"				=> "email/frontend/donate_form.php",
														"email_subject"				=> "Donation Form",
														"default_subject"			=> TRUE,
														"email_post"				=> $_POST,
														"email_cc"					=> "Imifinance786@gmail.com,sakinarizviimi@gmail.com,
														imiwaiting@att.net,tehzeeb.fatima@genetechsolutions.com,imihq@imamiamedics.com"
														);
					
					$is_email_sent				= $ci->_send_email( $email_template );
					#to_user
					
					// create tax receipt pdf and send it to user.
					// self::send_tax_receipt($donationId, $ci);
					$ci->functions->send_tax_receipt($donationId, $ci);
					
					#test email
					$message 					= '<strong>DONATE FORM:</strong> test _ payment ' . serialize( $_POST ) ;
					$email_template				= array("email_to"				=> 'ali.nayani@genetechsolutions.com',
														"email_heading"			=> 'DONATION FORM',
														"email_file"			=> "email/global/_blank_page.php",
														"email_subject"			=> '---> DONATION FORM',
														"email_post"			=> array("content"		=> $message) );
					
					//$is_email_sent				= $ci->_send_email( $email_template );
					#test email
					
					
					
					#*************************** *************************** *************************** 
					#*************************** 	    EMAIL PREPARATION 	 *************************** 
					#*************************** *************************** *************************** 

					// $data['_messageBundle']				= $ci->_messageBundle(
					// 	'success_big',
					// 	"Thank you for your gift. Tax receipts will be mailed no later than three to four weeks after your donation has been processed.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612",
					// 	"You’re what making a difference looks like"
					// );

					// $data['_pageview']						= "global/_blank_page.php"; //cc

					$data['custom_donate_ty_data']	= array(
						'title'		=> 'You’re what making a difference looks like',
						'message'	=> 'Thank you for your gift. We will send you the tax receipts.<br/><br/>Imamia Medics International<br/>EIN: 22330 920 8010 612',
					);

					$data['_pageview']						= "global/_custom_donate_thankyou_page.php";
					$data['is_donation_success']			= "yes";

					unset($_POST);

					return true;
				}
		 	}
		}
	}

	// static public function form_dropdown_custom($name = '', $options = array(), $selected = array(), $extra = '', $ci = null)
	// {
	// 	if ( ! is_array($selected))
	// 	{
	// 		$selected = array($selected);
	// 	}

	// 	if (count($selected) === 0)
	// 	{
	// 		if (isset($_POST[$name]))
	// 		{
	// 			$selected = array($_POST[$name]);
	// 		}
	// 	}

	// 	if ($extra != '') $extra = ' '.$extra;

	// 	$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

	// 	$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";

	// 	foreach ($options as $key => $val)
	// 	{
	// 		$key = (string) $key;
			
	// 		$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

	// 		$form .= '<option value="'.$key.'"'.$sel.' class="'.$val[1].'" type="'.$val[2].'">'.(string) $val[0]."</option>\n";
	// 	}

	// 	$form .= '</select>';

	// 	return $form;
	// }

	
	static public function form_dropdown_custom($name = '', $options = array(), $selected = array(), $extra = '', $ci = null)
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		if (count($selected) === 0)
		{
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') $extra = ' '.$extra;

		$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

		$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";

		if($options['0']){
			unset($options['0']);
		}
		foreach ($options as $key => $value) {
			if($value[3] != 0){
				$options[$value[3]][$key] = $value;
				unset($options[$key]);
			}
		}
		uasort($options, function($a, $b) {
			return $a[0] > $b[0];
		});

		foreach ($options as $key => $val)
		{
			$key = (string) $key;
			$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

			if(count($val) > 4){
				$sub_group = array_diff_key($val, array_flip([0,1,2,3]));
				asort ($sub_group);
				$form .= '<optgroup label="'.(string) $val[0].'">';
				foreach ( $sub_group as $k => $row )
				{
					$form .= '<option value="'.$k.'" class="'.$row[1].'" type="'.$row['type'].'">'.$row[0]."</option>\n";
				}
				$form .= "</optgroup>";
			}else {
				$form .= '<option value="'.$key.'"'.$sel.' class="'.$val[1].'" type="'.$val[2].'">'.(string) $val[0]."</option>\n";
			}
		}

		$form .= '</select>';

		return $form;
	}

	static public function getIPLocation()
	{
		$ip = $_SERVER["REMOTE_ADDR"];
		$ch = curl_init('http://ipwhois.app/json/'.$ip);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($ch);
		curl_close($ch);

		// Decode JSON response
		$ipwhois_result = json_decode($json, true);
		// Country code output, field "country_code"
		return strtolower($ipwhois_result['country']);

	}

	/*
	static public function send_tax_receipt($df_id, $ci){

		// pdfwork start			
		$TMP_receipt	= $ci->db->query("SELECT df.*, dcc.comment as comments, dpp.name as dpdesc, dc.name as home_city_name, ds.name as home_state_name FROM tb_donation_form df LEFT JOIN `tb_dc_comments` dcc ON df.id = dcc.df_id LEFT JOIN tb_donation_projects dpp ON dpp.id = df.donation_projects_id LEFT JOIN tb_cities dc ON dc.id = df.home_city LEFT JOIN tb_states ds ON ds.id = df.home_state WHERE df.id = '". $df_id ."' ");			
		
		if( $TMP_receipt->num_rows() > 0 && ($TMP_receipt->row()->belongs_country == '2' || $TMP_receipt->row()->belongs_country == '3') )
		{

			$belongs_to  = $TMP_receipt->row()->belongs_country; // belongs country
			// canada
			if($belongs_to == '3'){
				$receipt_pdf 	= "global/receipt-canada.php";
				$receipt_email 	= "email/frontend/donate_receipt_canada.php";
				$receipt_prefix = 'C';
				$email_bcc		= array("imicanada@gmail.com","nooranimd@yahoo.com","drsyahaider@yahoo.ca","Donnarmoz@gmail.com","imifinance786@gmail.com","imihq@imamiamedics.com","imiwaiting@att.net");
			}
			// international
			else{
				$receipt_pdf 	= "global/receipt.php";
				$receipt_email 	= "email/frontend/donate_receipt.php";
				$receipt_prefix = 'A';
				$email_bcc		= "imifinance786@gmail.com";
			}

				$get_max_receipt_no	= $ci->db->query("SELECT MAX(tax_receipt_num) AS largest_receipt_no FROM `tb_donation_form` WHERE belongs_country = ".$belongs_to);
				// $get_max_receipt_no	= $ci->db->query("SELECT MAX(tax_receipt_num) AS largest_receipt_no FROM `tb_donation_form`");
				if( count($get_max_receipt_no->row()) > 0 ){
					$max_receipt_num	= intval($get_max_receipt_no->row()->largest_receipt_no);
					// $max_receipt_num	= $max_receipt_num + 1;

					if($belongs_to == '3'){
						$max_receipt_num	= ($max_receipt_num <= 120000) ? 120001 : $max_receipt_num + 1;
					} else {
						$max_receipt_num	= ($max_receipt_num <= 110000) ? 110001 : $max_receipt_num + 1;
					}

					$editDataForReceipt	= array();
					$editDataForReceipt['tax_receipt_num']= $max_receipt_num;
					$editDataForReceipt['id']=$df_id;

					$ci->queries->SaveDeleteTables($editDataForReceipt, 'e', "tb_donation_form", 'id');
				}

			$_homestate	= ($TMP_receipt->row()->home_state_name) ? ', ' . $TMP_receipt->row()->home_state_name : "";
			$pdfData = array(
				"name"			=> $TMP_receipt->row()->first_name,
				"address"		=> $TMP_receipt->row()->home_city_name . $_homestate,
				// "address"		=> $TMP_receipt->row()->home_address,
				"email"			=> $TMP_receipt->row()->email,
				"project"		=> $TMP_receipt->row()->dpdesc,
				"amount"		=> $TMP_receipt->row()->donate_amount,
				"date" 			=> date("Y-m-d", strtotime( $TMP_receipt->row()->date_added)),
				"serial_num" 	=> $receipt_prefix . $max_receipt_num
			);
			
			$file_name = './assets/temp-tax-files/tax-receipt-' . $df_id . '.pdf';
			$html_code = '<link rel="preconnect" href="https://fonts.googleapis.com">';
			$html_code .= $ci->load->view( $receipt_pdf, $pdfData, TRUE );
			$pdf = new Pdf();
			$pdf->load_html($html_code);
			$pdf->render();
			$file = $pdf->output();
			file_put_contents($file_name, $file);
			
			// "email_attachment"		=> $upload_dir = SERVER_ABSOLUTE_PATH_IMICONF . "./assets/files/arbaeen-mission/".$__data['cv_resume'],
			$email_template		= array("email_to"					=> $TMP_receipt->row()->email,
										"email_heading"				=> "Donation Tax Receipt",
										"email_file"				=> $receipt_email,
										"email_subject"				=> "Your Tax Receipt for the donation made to Imamia Medics International",
										"default_subject"			=> TRUE,
										"email_post"				=> $_POST,
										// "email_bcc"					=> ( SessionHelper::_get_session("EMAIL_TO", "site_settings") ),
										"email_bcc"					=> $email_bcc,
										"email_attachment"			=> $file_name
										);

			$is_email_sent_2				= $ci->_send_email( $email_template );
			unlink($file_name);
		
		}
		// pdfwork end
	}
	*/
}