<style>
.error_style{ display:none;}
.inline_error_style .error_style{ display:block;}
</style>

<div class="screen_one_status">
    <div class="conf-reg-page-top">
        <h1><?php echo lang_line('text_conferenceregistration');?></h1>
        <p><?php echo conference_fullname( $conference );?></p>
        <span><?php echo conference_durationdates( $conference );?> </span>
    </div>
    
    


  	<?php $this->load->view("frontend/template/_confregistrationbreadcrumbs.php");?>
    
    
    
    <p>&nbsp;</p>
    
    
    
    
    
    <?php
    $attributes 			= array("method"		=> "post",
                                    "enctype"		=> "multipart/form-data",
                                    "name"			=> "form1",
                                    "id"			=> "form1");
    
    echo form_open(site_url( uri_string() ), $attributes);
	
	
	
	
	$data["_messageBundle"]				= $_messageBundle2;
	?>
	<div class="inline_error_style"><?php $this->load->view('frontend/template/_show_messages.php', $data); ?> </div>
    
    
    <input  class="bluebuttons btnSubmit" name="submitform" type="submit" value="Submit" style="display:none;" />
    
    
    
    
    <div class="conf-reg-page-table">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="48%">
    				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="20%"><label>Suffix  :</label>                              <?php echo required_field('fontsize_1-4em');?></td>
                            <td width="2%">&nbsp; </td>
                            <td width="73%">
                            <?php
                            echo form_dropdown('education', DropdownHelper::education_dropdown(), set_value("education", $education), "class='form-control ". set_class( 'education' ) ." '" );
                            echo form_error("education");
                            ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>&nbsp; </td>
                            <td>&nbsp; </td>
                            <td>&nbsp; </td>
                        </tr>
                        
                        <tr>
                            <td><label>Name  : <?php echo required_field('fontsize_1-4em');?> </label> </td>
                            <td>&nbsp; </td>
                            <td width="73%"><?php
                            $tmp_name		= 'name';
                            $specdata		= array("name"			=> $tmp_name,
                                                    "id"			=> $tmp_name,
                                                    
                                                    "type"			=> "text",
                                                    "class"			=> set_class( $tmp_name ),
                                                    "value"			=> set_value( $tmp_name, $$tmp_name ),
                                                    "placeholder"	=> "Name");	
                    
                            echo form_input($specdata);
                            echo form_error( $tmp_name );
                            ?>
                              <small class="passport_note">(as it appears on your passport)</small></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <td><label>Email : <?php echo required_field('fontsize_1-4em');?></label></td>
                          <td>&nbsp;</td>
                          <td><?php
                            $tmp_name		= 'email';
                            $specdata		= array("name"			=> $tmp_name,
                                                    "id"			=> $tmp_name,
                                                    
                                                    "type"			=> "text",
                                                    "class"			=> set_class( $tmp_name ),
                                                    "value"			=> set_value( $tmp_name, $$tmp_name ),
                                                    "placeholder"	=> "Email");	
                    
                            echo form_input($specdata);
                            echo form_error( $tmp_name );
                            ?></td>
                        </tr>
                        
                  </table>
                </td>
                
              <td width="8%">&nbsp; </td>
                <td width="48%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="20%"><label>Title   : <?php echo required_field('fontsize_1-4em');?></label></td>
                    <td width="2%">&nbsp;</td>
                    <td width="73%"><?php
                            echo form_dropdown('prefix', DropdownHelper::prefix_dropdown(), set_value("prefix", $prefix), "class='form-control ". set_class( 'prefix' ) ." '" );
                            echo form_error("prefix");
                            ?></td>
                </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                  <tr>
                    <td><label>Phone : <?php echo required_field('fontsize_1-4em');?></label></td>
                    <td>&nbsp;</td>
                    <td width="73%"><?php
                            $tmp_name		= 'phone';
                            $specdata		= array("name"			=> $tmp_name,
                                                    "id"			=> $tmp_name,
                                                    
                                                    "type"			=> "text",
                                                    "class"			=> set_class( $tmp_name ),
                                                    "value"			=> set_value( $tmp_name, $$tmp_name ),
                                                    "placeholder"	=> "Phone");	
                    
                            echo form_input($specdata);
                            echo form_error( $tmp_name );
                            ?></td>
                </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
              </table></td>
            </tr>
        </table>
    </div>
    
    
    <div class="conf-reg-page-table">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            <td colspan="11">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="35%;"><strong>Number of Family Members Accompanying you?</strong></td>
                        <td width="1%">&nbsp;</td>
                        <td width="15%">
                        <?php
                        $arrayindex				= NumberHelper::number_array( range("1", "8") );
                        $arrayindex['']			= 'Select';
                        ksort($arrayindex);
                        echo form_dropdown( 'no_of_family_members', 
                                            $arrayindex, 
                                            set_value("no_of_family_members", $no_of_family_members), 
                                            "onchange='document.form1.submit();' class='form-control ". set_class( 'no_of_family_members' ) ." '" );
						
						
						$TMP_no					= 0;
						if ( $conferenceregistration_screenone->num_rows() > 0 )
						{
							$TMP_no				= $conferenceregistration_screenone->row("no_of_family_members");
						}
                        ?>
                        
                        <input type="hidden" name="hdn_no_of_family_members" value="<?php echo $TMP_no;?>"  />
                        </td>
                        <td width="53%">&nbsp;</td>
                    </tr>
                </table>
            </td>
            </tr>
            
            
            <?php
            if ( set_value("no_of_family_members", $no_of_family_members) )
            {
            ?>
                <tr>
                    <td width="12%">&nbsp;</td>
                    <td width="2%">&nbsp;</td>
                    <td width="28%">&nbsp;</td>
                    <td width="2%">&nbsp;</td>
                    <td width="29%">&nbsp;</td>
                    <td width="2%">&nbsp;</td>
                    <td width="10%">&nbsp;</td>
                    <td width="2%">&nbsp;</td>
                    <td width="9%">&nbsp;</td>
                    <td width="2%">&nbsp;</td>
                    <td width="5%">&nbsp;</td>
                </tr>
        
        
                <tr>
                    <td height="30">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>Name : <?php echo required_field('fontsize_1-4em');?></td>
                    <td>&nbsp;</td>
                    <td>Email : <?php echo required_field('fontsize_1-4em');?></td>
                    <td>&nbsp;</td>
                    <td>Relationship :</td>
                    <td>&nbsp;</td>
                    <td>Age :</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
        
            
                <?php
                for($i=0; $i < set_value("no_of_family_members", $no_of_family_members); $i++)
                {
                    $index			= $i;
                    $index++;
                    ?>
                        <tr>
                            <td>Family Member <?php echo $index;?></td>
                            <td>&nbsp;</td>
                            <td>
                            <?php
                            $tmp_name		= 'family_name['. $i .']';
                            $specdata		= array("name"			=> $tmp_name,
                                                    "id"			=> $tmp_name,
                                                    
                                                    "type"			=> "text",
                                                    "class"			=> set_class( $tmp_name ),
                                                    "value"			=> set_value( $tmp_name, $family_name[$i] ),
                                                    "placeholder"	=> "Name");	
                    
                            echo form_input($specdata);
                            echo form_error( $tmp_name );
                            ?>
                            
                            <?php
                            $tmp_name		= 'family_id['. $i .']';
                            $specdata		= array("name"			=> $tmp_name,
                                                    "id"			=> $tmp_name,
                                                    
                                                    "type"			=> "hidden",
                                                    "class"			=> set_class( $tmp_name ),
                                                    "value"			=> set_value( $tmp_name, $family_id[$i] ),
                                                    "placeholder"	=> "Family ID");	
                    
                            echo form_input($specdata);
                            echo form_error( $tmp_name );
                            ?>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                            <?php
                            $tmp_name		= 'family_email['. $i .']';
                            $specdata		= array("name"			=> $tmp_name,
                                                    "id"			=> $tmp_name,
                                                    
                                                    "type"			=> "text",
                                                    "class"			=> set_class( $tmp_name ),
                                                    "value"			=> set_value( $tmp_name, $family_email[$i] ),
                                                    "placeholder"	=> "Email");	
                    
                            echo form_input($specdata);
                            echo form_error( $tmp_name );
                            ?>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                            <?php
                            $tmp_name				= 'family_relationship['. $i .']';
                            echo form_dropdown($tmp_name, 
                                               DropdownHelper::relationship_dropdown(), 
                                               set_value($tmp_name, $family_relationship[$i]), 
                                               "class='form-control ". set_class( $tmp_name ) ." '" );
                            ?>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                            <?php
                            $tmp_name				= 'family_age['. $i .']';
                            $arrayindex				= NumberHelper::number_array( range("1", "90") );
                            echo form_dropdown($tmp_name, 
                                               $arrayindex, 
                                               set_value($tmp_name, $family_age[$i]), 
                                               "class='form-control ". set_class( $tmp_name ) ." '" );
                            ?>
                            </td>
                            <td align="right">&nbsp;</td>
                            <td align="right">
                            <?php
							if ( set_value( 'family_id['. $i .']', $family_id[$i] ) != '' )
							{
							?>
                            <input type="image"  src="<?php echo base_url("assets/frontend/images/delete_icon.png");?>"  name="delete[<?php echo set_value( $tmp_name, $family_id[$i] );?>]" value="delete" />
                            <?php
							}
							?>
                            </a>
                            </td>
                        </tr>
        
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td colspan="3">&nbsp;</td>
                        </tr>
                    <?php
                }
            }
            ?>
            
    
            
        </table>
    </div>
    
            
    <div class="flt-rgt">
    	
        
        <label style="display:none;">
			<?php $tmp_name = 'travelling_with';?>
            <input type="radio" name="<?php echo $tmp_name;?>"  checked="checked" value="imi_group"    />
            imi_group
        </label>   
        
        
        
        <input  name="id" type="hidden" value="<?php echo set_value("id", $id);?>" />
        <input  class="bluebuttons"  type="button" onclick="$('.btnSubmit').click();" value="<?php echo lang_line('text_saveandproceed');?>" />
    </div>
    
    <?php
    echo form_close();
    ?>
</div>