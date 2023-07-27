<?php 
$attributes 			= array("method"		=> "post",
								"enctype"		=> "multipart/form-data");
$unique_form			= array("unique_formid"	=> set_value("unique_formid", random_string("unique")) );

echo form_open(site_url( $_directory . "controls/save" ), $attributes, $unique_form);
?>    

	<table class="table table_form">
		

		
        
       
		
		<tr>
				<td class="td_bg fieldKey">User Name <?php echo required_field(); ?></td>
				<td class="td_bg fieldValue" colspan="2">
                <div class="input-group">
				<?php
				$specdata		= array("name"		=> "username",
										"id"		=> "username",
										"size"		=> 50,
										"class"		=> "form-control",
										"value"		=> set_value("username", $username) );	
				
				echo form_input($specdata);
				?>
                </div>
				</td>
			</tr>
            
            
       	<tr>
            <td class="td_bg fieldKey">Menu <?php echo required_field(); ?></td>
            <td class="td_bg fieldValue" colspan="2">
            <div class="input-group">
                
                <select name="menu[]" multiple="multiple" class="form-control" size="10">
                	<!--<option value="0" <?php //echo in_array('0', explode(",",$menu)) ? 'selected="selected"' : ''; ?>>All Menus</option>-->
					<?php 
					$menu_list = DropdownHelper::menu_dropdown( FALSE, TRUE ); 
					foreach($menu_list as $mk => $mv) {
					?>
                    	
						<?php 
						if($_POST['menu']) 
						{ 
							$pmenu = explode(",",set_value("menu")); 
							?>
	                    	<option value="<?php echo $mk; ?>" <?php echo in_array($mk, $pmenu) ? 'selected="selected"' : ''; ?>><?php echo $mv; ?></option>
                        <?php 
						} 
						else 
						{ 
						?>
                        	<option value="<?php echo $mk; ?>" <?php echo in_array($mk, explode(",",$menu)) ? 'selected="selected"' : ''; ?>><?php echo $mv; ?></option>
                        <?php 
						} 
						?>
                        
                    <?php	
					}
					?>
                </select>
                
            </div>
            </td>
        </tr>
		<tr>
            <td class="td_bg fieldKey">Event <?php echo required_field(); ?></td>
            <td class="td_bg fieldValue" colspan="2">
            <div class="input-group">
                
                <select name="events[]" multiple="multiple" class="form-control" size="10">
                	<!--<option value="0" <?php //echo in_array('0', explode(",",$menu)) ? 'selected="selected"' : ''; ?>>All Menus</option>-->
					<?php 
					
					$events_list = DropdownHelper::events_dropdown( TRUE );

					foreach($events_list as $mk => $mv) {
					?>
                    	
						<?php 
						if($_POST['events']) 
						{ 
							$pmenu = explode(",",set_value("events")); 
							?>
	                    	<option value="<?php echo $mk; ?>" <?php echo in_array($mk, $pmenu) ? 'selected="selected"' : ''; ?>><?php echo $mv; ?></option>
                        <?php 
						} 
						else 
						{ 
						?>
                        	<option value="<?php echo $mk; ?>" <?php echo in_array($mk, explode(",",$events)) ? 'selected="selected"' : ''; ?>><?php echo $mv; ?></option>
                        <?php 
						} 
						?>
                        
                    <?php	
					}
					?>
                </select>
                
            </div>
            </td>
        </tr>
		
		<tr>
		  <td class="td_bg fieldKey">Email  </td>
		  <td class="td_bg" colspan="2">
          	<div class="input-group">
			<?php
            $specdata		= array("name"		=> "email",
                                    "id"		=> "email",
									"size"		=> 50,
                                    "class"		=> "form-control",
                                    "value"		=> set_value("email", $email) );	
            
            echo form_input($specdata);
            ?>
            </div>
          
          </td>
	  </tr>
		
      
      <tr>
		  <td class="td_bg">Testimonial <?php echo required_field(); ?></td>
		  <td colspan="2" class="td_bg">
          
          	<div class="input-group">
				<?php
				$specdata		= array("name"		=> "testimonial",
										"id"		=> "testimonial",
										"rows"		=> 10,
										"cols"		=> 70,
										"class"		=> "ckeditor1 form-control",
										"value"		=> set_value("testimonial", $testimonial) );	
				
				echo form_textarea($specdata);
				?>
                </div>
                
          
            </td>
	  </tr>
      
      
      
		<tr>
		  <td class="td_bg">Status</td>
		  <td colspan="2" class="td_bg"><div class="input-group">
		  <?php echo form_dropdown('status', DropdownHelper::yesno_dropdown(), set_value("status", $status), "class='form-control '" )?></div>
          </td>
	  </tr>
		
		
		
		<tr>
			<td class="td_bg">&nbsp;</td>
			<td colspan="2" class="td_bg">
				<input type="hidden" value="<?php echo set_value("id", $id);?>" name="hdn_id" />
			</td>
		</tr>
		
		
  </table>
<input type="hidden" name="id" value="<?php echo set_value('id', $id); ?>" />
  <input type="hidden" name="options" value="<?php echo set_value('options', $options); ?>" />
    
    <div class="crud_controls">
        <button type="submit" data-operationid="managetestimonialssave" class="btn btn-warning btn-flat"><?php echo lang_line("text_save");?></button>
        <a class="btn btn-danger btn-flat" data-operationid="managetestimonialsview" href="<?php echo site_url( $_directory . "controls/view");?>">
            <?php echo lang_line("text_cancel");?>
        </a>
    </div>

</form>