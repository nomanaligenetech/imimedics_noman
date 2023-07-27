<?php $this->load->view('frontend/template/_head.php');

$lang = getCurrentLang($content_languages);
?>

<body lang="<?php echo $lang['code']; ?>" dir="<?php echo $lang['direction']; ?>" class="<?php echo is_countryCheck(true); ?> <?php echo $lang['direction']; ?> lang-<?php echo $lang['code']; ?>">


   	<div class="menu_active" style="display:none;">
    	<h1><?php echo $menu_active;?></h1>	
    </div>
    
    
    
    <div class="hwrap header">
        <div class="cont2 pos_rel bg_white">
            
        	<?php
			if ($showThings['_show_HEADER'])
			{
				$this->load->view('frontend/template/_header.php');
			}
			
			if ($showThings['_show_SLIDER'])
            {
               
            }
			 $this->load->view('frontend/template/_static_slider.php');
			?>
            
        </div>
    </div>
    
    
    
    
    
    
    
    
    
    <div class="hwrap mainBody">

		<?php $this->load->view('frontend/template/_breadcrumbs.php'); ?>
        
		
		
        <div class="cont2">
            <div class="inner_content bg_Offwhite p_TopBottom30">
                <div class="cont1 Two_Col_Tem1">

                    <div class="right_area cmsarea fl_rit w_100">
                    	
                        <?php
						$this->load->view('frontend/template/_show_messages.php');
						?>
						
                        <?php echo $this->load->view($_pageview); ?>
                        
                    </div>
                
                </div>
            </div> 
        </div>
  
	</div>
    
    <?php
	if ($showThings['_show_FOOTER'])
	{
		$this->load->view('frontend/template/_footer.php');
	}	
	?>
    

</body>
</html>	