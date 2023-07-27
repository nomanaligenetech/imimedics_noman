function copyHTML_input()
{
	
	$("div.reg-ticket-rightsection").html(function(i,t){
		
		var text 	= t.replace('In Your Cart','Payment Summary from Step 2 of Registration form');
		text		= text.replace('Payable Now','Paid');
		text		= text.replace('Total Payable','Total Paid');
		text		= text.replace('Cash OnSite','Payable OnSite');
		//console.log(text);
		$("input[name='email_text']").val( text );
		
	});
		
	
	//$("input[name='email_text']").val( $("div.reg-ticket-rightsection").html() );	
}

// function toggle_not_a_member( elem )
// {

// 	if(!$(elem).is(':checked')){
// 		$('li.non_member_data').css("display", "block");
// 		$('li.non_member_data > .pak_step-form-package-type > input[type="radio"]').trigger('click');
// 		$('li.member_data').css("display", "none");
// 	} else {
// 		$('li.member_data').css("display", "block");
// 		$('li.member_data > .pak_step-form-package-type > input[type="radio"]').trigger('click');
// 		$('li.non_member_data').css("display", "none");
// 	}
// }

function calculate_conferenceregistrationpricing()
{
	
	
	var _total_amt			= 0;
	var imimember_fee		= 0;
	var is_cash				= false;
	
	$("ul.confreg_price_selection > li input[type='radio']").each(function(){
														   
		
		if ( $(this).parent().parent().find("input[type='radio']").attr("data-percentage") == 100 )
		{
			is_cash			= true;	
		}


		if ( $(this).attr("checked") )
		{
			sel_parent = $(this).parent().parent().parent();
			_total_amt = 0
			tmp_p = 1
			if(sel_parent.data('tab-type') == 'g-reg'){
				$('.reg-ticket-section-table .pkg-table tr').each(function(){

					if($(this).find('.weight input').val() > 0){
						var tmp_p = $(this).find('.pak_step-form-package-input').data('price');

						tmp_p = tmp_p * $(this).find('.weight input').val();
						_total_amt += tmp_p;
					}

				});
			}
			// console.log(( !$(this).attr("data-isimi") ) || (IS_MEMBER_or_OTHER == 'others'));
			var IS_MEMBER_or_OTHER				= $("input[name='hdn_options_selected']").val();
			if ( ( !$(this).attr("data-isimi") ) || (IS_MEMBER_or_OTHER == 'others') )
			{
				
				if ( $("input[name='be_a_member']:checked").length > 0 )
				{
					$(".reg-ticket-section-content input[type='radio']:checked").each(function(){
						
						imimember_fee			= parseFloat( $(this).attr("data-price") );
					});
				}
				
			}


		}
		
		
		
	});
	
	
	
	var IS_MEMBER_or_OTHER				= $("input[name='hdn_options_selected']").val();
	if ( IS_MEMBER_or_OTHER == "others" )
	{
		var abspaid_lessthis			= 0;
		if ( $(".tr_abspaid").length > 0 )
		{
			abspaid_lessthis			= $("input[name='txt_abs_paid']").val();	
		}
		
		var payable_now			= (_total_amt + imimember_fee - abspaid_lessthis);
		var cash_onsite			= 0;
		var total_payable		= (_total_amt + imimember_fee - abspaid_lessthis);
	}
	else
	{
		//1200 * 40% = 480
		var after_discount		= ( _total_amt ) * parseFloat( $(".js_payable_now_perc").html() ) / 100;
		
		//480 
		var payable_now			= ( after_discount );
		
		var cash_onsite			= ( _total_amt   ) - after_discount  ;
		
		var total_payable		= (  after_discount + imimember_fee );
	}

	$(".js_package_fee span").html( numberWithCommas( _total_amt.toFixed(0) ) );
	$("input[name='txt_package_fee']").val( _total_amt );
	
	
	$(".js_payable_now span").html( numberWithCommas( payable_now.toFixed(0) ) );
	$("input[name='txt_payable_now']").val( payable_now );
	
	
	$(".js_cash_onsite span").html( numberWithCommas( cash_onsite.toFixed(0)  ) );
	$("input[name='txt_cash_onsite']").val( cash_onsite );
	
	
	$(".js_total_payable span").html( numberWithCommas( total_payable.toFixed(0) ) );
	$("input[name='txt_total_payable']").val( total_payable );
	
	
	if ( is_cash )
	{
		$(".js_payable_now span").html( numberWithCommas( total_payable.toFixed(0) ) );
		$("input[name='txt_payable_now']").val( total_payable );
	}
	
	copyHTML_input();
	
	
}

function numberWithCommas(x) 
{
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


function toggle_imimember_fee_display_in_cart( elem )
{
	
	if ( elem.is(":checked")  )
	{
		$("ul.not_a_member > li > div").slideDown("slow");
		$(".js_not_a_member_fee span").html("0.00");
		
		
		$("tr.tr_nam").show();	
	}
	else
	{
		$("ul.not_a_member > li > div").slideUp("slow");
		$("div.options ul li input").attr("checked", false);		
		
		
		
		$("tr.tr_nam input[name='txt_not_a_member_fee']").val(0);
		$("tr.tr_nam .js_not_a_member_fee span").html( 0 );
		$("tr.tr_nam").hide();
	}	
	
	
	$(".reg-ticket-section-content input[type='radio']:checked").click();
	
	
}

function calculate_imimember_fee_in_cart( elem )
{
	if ( elem == null)
	{	
		return;
	}
	if ( elem.is(":checked") && $(".reg-ticket-section-content input[type='checkbox']:checked").length > 0 )
	{
		var _txt	= $(".reg-ticket-section-content input[type='radio']:checked").parent().find("span:eq(0)").html();
		$("tr.tr_nam small").html( _txt );
		
		var amt		= $(".reg-ticket-section-content input[type='radio']:checked").parent().find("span.fee").html();
		$(".js_not_a_member_fee span").html( parseFloat( amt ).toFixed(0) );
		$("input[name='txt_not_a_member_fee']").val( amt );
		
		
		$("tr.tr_nam").show();	
	}
	else
	{
		$("tr.tr_nam input[name='txt_not_a_member_fee']").val(0);
		$("tr.tr_nam .js_not_a_member_fee span").html( 0 );
		$("tr.tr_nam").hide();
	}	
}

function addons_checkbox( _this )
{
	_this.each (function(){
			
		if ( $(this).prop("checked")  )
		{
			$(this).parent().parent().find(" ul input[type='checkbox'] ").attr("disabled", false);	
		}
		else
		{
			$(this).parent().parent().find(" ul input[type='checkbox'] ").attr("disabled", true);	
			$(this).parent().parent().find(" ul input[type='checkbox'] ").attr("checked", false);	
		}
			
	});
	
	
		
}

$(document).ready(function(){

	var IS_MEMBER_or_OTHER				= $("input[name='hdn_options_selected']").val();
	
	if ( $("ul.confreg_price_selection > li > label").length > 0 )
	{
		//return false;
		
		$("ul.confreg_price_selection > li input[type='radio']").click(function(){
			$(this).parent().click();
				
		});
		
		
		$("ul.confreg_price_selection  li.parent_package > p ").click(function(e){

			
			//console.log("CLicked");
			//console.log($(this));
			
			
			$("ul.confreg_price_selection  li.parent_package  ").addClass("closed");
			
			if ( $(this).parent().find( " > ul " ).css("display") == "block" )
			{
				$(this).parent().find( " > ul " ).slideUp("slow");	
			}
			else
			{
				$(this).parent().find( " > ul " ).slideDown("slow");	
				//console.log($(this).parent());
				$(this).parent().removeClass("closed");
			}
			
			
			
		});
		
		
		
		/* $("ul.confreg_price_selection > li > label").click(function(){
			
			
			$(this).parent().find("input[type='checkbox']input[data-isfree='1']").click(function( event ){
				event.preventDefault();
			});
			
			
			if ( $(this).hasClass("current") )
			{
				//no nothing
			}
			else
			{				
		
				if ( $(this).parent().find("input[type='radio']").attr("disabled")  )
				{
					
				}
				else
				{
					
					//clear all radio (checked false)
					$("ul.confreg_price_selection > li input[type='radio']").attr("checked", false);
					//$("ul.confreg_price_selection > li > ul input[type='checkbox']").attr("disabled", true);
					
					
					//make all checkbox disabled which are Free
					$("ul.confreg_price_selection > li > ul input[type='checkbox']").not( $("ul.confreg_price_selection > li > ul input[type='checkbox'][data-isfree='1']") ).attr("disabled", true);
					
					
					
					
					
					
					//make selected accordion section checked true
					$(this).parent().find("input[type='radio']").attr("checked", true);
					//$(this).parent().find("input[type='checkbox']").attr("disabled", false);
					
					
					//make all checkbox enabled which are not free
					$(this).parent().find("input[type='checkbox']").not($(this).parent().find("input[type='checkbox'].child-addon")).not( $(this).parent().find("input[type='checkbox'][data-isfree='1']") ).attr("disabled", false);
					//$(this).parent().find("input[type='checkbox'].child-addon").attr("disabled", true);
					
					
					
					//set percentage in span
					var _data_percentage	= $(this).parent().find("input[type='radio']").attr("data-percentage");
					
					
					//console.log(_data_percentage);
					$("span.js_payable_now_perc").html( _data_percentage );
					if ( parseFloat(_data_percentage) > 0 )
					{
						$("span.parent_js_payable_now_perc").show();
					}
					else
					{
						$("span.parent_js_payable_now_perc").hide();	
					}
					//$("span.parent_js_payable_now_perc").html( _data_percentage );
				}
				
				
				if ( is_post || $("input[name='id']").val() != '' )
				{
					var _selected_li		= $(this).parent();
					//$("ul.confreg_price_selection > li").not( _selected_li ).find(" input[type='checkbox'] ").attr("checked", false);
					
					
					//make all checkbox disabled which are not active by this <li> (selection)
					$("ul.confreg_price_selection > li").not( _selected_li ).find(" input[type='checkbox'] ").not($("ul.confreg_price_selection > li").not( _selected_li ).find(" input[type='checkbox'][data-isfree='1'] ")).attr("checked", false);
					
					
				}
				else
				{
					//by default - when user land on screen 2 - make all checkbox FALSE
					$(this).parent().find("input[type='checkbox']").attr("checked", false);	
					
				}
				
				
				
				
				var is_imi				= $(this).parent().find("input[type='radio']").attr("data-isimi");
				
				//if user is travelling independently
				if (IS_MEMBER_or_OTHER == 'others')
				{
					//$("ul.not_a_member input").attr("checked", false);
					$("ul.not_a_member input").attr("disabled", false);
					
					$("ul.not_a_member").show();
					$("div.couponsubmitbtn").show();
				}
				else
				{
					
					//if traveling with group / and also a IMI member
					
					if ( is_imi )
					{
						//$("ul.not_a_member").hide();
						
						//$("ul.not_a_member input").attr("checked", false);
						//$("ul.not_a_member input").attr("disabled", true);
						
						
						
						//Show Coupon Button
						$("div.couponsubmitbtn").show();
						
						$("tr.tr_nam input[name='txt_not_a_member_fee']").val(0);
						$("tr.tr_nam .js_not_a_member_fee span").html( 0 );
						$("tr.tr_nam").hide();
						
					}
					else
					{
						
						$("div.couponsubmitbtn").hide();
						
						
						$("ul.not_a_member input").attr("checked", false)
						//$("ul.not_a_member").show();
						//$("ul.not_a_member input").attr("disabled", false);
					}	
				}
				
				
				
				$("ul.confreg_price_selection > li > label").removeClass("current");
				$(this).addClass("current");
				
				
				
				$("ul.confreg_price_selection > li > ul").hide();
				$(this).parent().find(" > ul ").show().find(" > li.parent_package:first > ul").slideDown();	
				$(this).parent().find(" > ul ").show().find(" > li.parent_package:first").removeClass("closed");
				//console.log("YYYY");
				//console.log($(this));
				
				addons_checkbox( $("ul.confreg_price_selection  li.this_is_package > label > input[type='checkbox']") );
				
				
				//$(this).parent().find("> ul > li:first > ul:first").slideDown("slow");
				//$(this).parent().find("> ul > li:first").removeClass("closed");
				
				//reset price calculation
				calculate_conferenceregistrationpricing();	
				toggle_imimember_fee_display_in_cart ( $(".reg-ticket-section-content input[type='checkbox']") );
				
			}
		}); */
		
		
		
		/* $("ul.confreg_price_selection  li.this_is_package > label > input[type='checkbox']").click(function(){

			
			addons_checkbox( $(this) );
			
		}); */
		

		
		/* $("ul.confreg_price_selection > li input[type='checkbox']").click(function(){

			calculate_conferenceregistrationpricing();				
		}); */
		
		$(".reg-ticket-section-table .no-of-persons").click(function(){
			
			calculate_conferenceregistrationpricing();				
			
		});
		
		
		if ( is_post || $("input[name='id']").val() != '' )
		{
			var lastselected		= $(".confreg_price_selection > li input[type='radio']:checked");
			lastselected.click();
			
			
			toggle_imimember_fee_display_in_cart ( $(".reg-ticket-section-content input[type='checkbox']") );
			calculate_imimember_fee_in_cart( $(".reg-ticket-section-content input[type='radio']") );
			
		}
		else
		{
			//click on page load (screen two)
			$(".confreg_price_selection > li input[type='radio']").not(":disabled").eq(0).click();
		}
		
		
		
		
		$(".reg-ticket-section-content input[type='checkbox']").click(function(){
			
			toggle_imimember_fee_display_in_cart( $(this) );
			calculate_conferenceregistrationpricing();
			
		});
		
		$(".reg-ticket-section-content input[type='radio']").click(function(){
			
			
			var show_IMI_li				= $(".confreg_price_selection > li input[type='radio'][data-isimi='1']").not(":disabled").eq(0);
			var show_NON_IMI_li			= $(".confreg_price_selection > li input[type='radio'][data-isimi='']").not(":disabled").eq(0);
			if ( show_IMI_li . length > 0 )
			{
				show_IMI_li.click();
			}
			
			calculate_imimember_fee_in_cart( $(this) );
			calculate_conferenceregistrationpricing();
		});
		
		

	}
	
	



	if ( $("input[name='hdn_no_of_family_members']").length > 0 )
	{
		$("select[name='no_of_family_members'] option").each(function(){
		
			if( $(this).val() < $("input[name='hdn_no_of_family_members']").val() )
			{
				$(this).attr("disabled", true);	
				$(this).css("background-color", "#ccc");	
				
			}
		
		});
	}
	
	if ( $(".participant_section").length > 0 )
	{
		$(".participant_section input").click(function(){
		
			$(this).closest('.pak_region-select-col').addClass('pak_region-active');
			
			if( $('select[name="regionid"]').has('option').length <= 0 ) {

				alert('Please wait. Page is loading..');
			}
			else
			{
				if ( $(this).val() == "1" )
				{
					$('select[name="regionid"]').prop("selectedIndex", 0);
				}
				else
				{
					$('select[name="regionid"]').prop("selectedIndex", 1);
				}
				
				//$("form[name='form1'] input[type='submit']").hide();
				
				$("form[name='form1']").submit();
			}
			
			//$(".conf-reg-page-content2-label span.desc").attr("class", "disactive desc");
			//$(this).parent().parent().find("span.desc").attr("class", "desc");
			
		});
	}

	$("span.add-sub").click(function(){

		if($(this).hasClass('sub-before')){
			input_elem    = $(this).next();
			input_val 	  = input_elem.val();
			min 		  = input_elem.attr('min');

			if(input_val > 0){
				input_val_new = parseInt(input_val) - 1;
				input_elem.val(input_val_new);
				console.log(input_elem.val());
			}
		}else if($(this).hasClass('add-after')){
			input_elem    = $(this).prev();
			input_val = input_elem.val();
			
			if(input_val < 10){
				input_val_new = parseInt(input_val) + 1;
				input_elem.val(input_val_new);
				console.log(input_elem.val());
			}
		}
	});
	if($('.reg-ticket-section-table.conf-reg-s-two-table').length > 0){
		
		$("span.add-sub").click(function(){
			// if($(this).hasClass('sub-before')){
	
			// }else if($(this).hasClass('add-after')){
			// 	// var price = $(this).parent('.no-of-persons').siblings('.pkg-prices > label > span').attr('data-price');
				var val;
				if($(this).hasClass('sub-before')){
					val = $(this).next().val();
				}else if($(this).hasClass('add-after')){
					val = $(this).prev().val();
				}

				var pkg_id 			= $(this).parent().parent().siblings('.pkg-prices').find('label').find('input').val();
				pkg_id = pkg_id.split('::')[0];
				var price 			= $(this).parent().parent().siblings('.pkg-prices').find('span').text();
				var title 			= $(this).parent().parent().siblings('.pkg-prices').find('span').attr('data-title');
				var pkg_name 		= $(this).parent().parent().siblings('.pkg-names').children('p').text();
				var is_addon  		= $(this).parent().parent().siblings('.pkg-prices').find('label').find('input[name="is_addon['+pkg_id+']"]').attr('value');
			
				console.log(is_addon); 
				if(val > 0){
					if($('#'+pkg_id+'').length > 0){
						$('.cart-table').find('tbody').find('#'+pkg_id).find('.prices').text( val +" X"+price) ;
					}else{
						if(is_addon == 0){

							$('.cart-table').find('tbody').find('tr:nth-child(2)').after("<tr id='"+pkg_id+"' data-pkg-id='"+pkg_id+"'><td height='35' width='60%' style='margin-bottom: 12px;'><strong>"+pkg_name+"</strong><p>"+title+"<p> </td><td height='35' width='50%' align='right' class='prices'>"+val+" X"+price+"</td></tr>");

						}else if(is_addon == 1){

							$('.cart-table').find('tbody').find('tr:nth-child(2)').after("<tr id='"+pkg_id+"' data-pkg-id='"+pkg_id+"'><td height='35' width='60%' style='margin-bottom: 12px;'><strong>"+title+"</strong><p>"+pkg_name+"<p> </td><td height='35' width='50%' align='right' class='prices'>"+val+" X"+price+"</td></tr>");
						}
						// console.log($('.cart-table').find('tbody').find('tr:nth-child(1)'));

					}
				}
				else if(val == 0){
					$('.cart-table').find('tbody').find('#'+pkg_id).remove();
				}
			// }
		});
	}
	$('.add-after.add-member').click(function(){
		$('#form1').submit();
	});
	$('.sub-before.remove-member').click(function(){
		$('#form1').submit();
	});
	$(".reg-ticket-section-tab").click(function(){

		elem = $(this);
		
		if(!elem.hasClass('active-tab')){
			elem.addClass('active-tab');
			if(elem.data('tab-type') == 'g-reg'){
				$('.reg-ticket-section-wrapper').show();
				$('.reg-ticket-section-content').hide();
			}else if(elem.data('tab-type') == 'no-mem'){
				$('.reg-ticket-section-content').show();
				$('.reg-ticket-section-wrapper').hide();
			}
			elem.siblings().each(function(){
				if($(this).hasClass('active-tab')){
					$(this).removeClass('active-tab');
				}
			});
		}
		// $('.reg-ticket-section-content .options').css("display", "block");
		
		// main_parent = $(elem).parent().parent().parent();
		// main_parent.addClass('active-tab');
		// main_parent.prev().removeClass('active-tab');

		
	});
	$('.conf-reg-s-two-table .conf-table-head th').on('click', function(){
		selected_elem = $(this).parent().next();
		if(selected_elem.hasClass('not-active')){
			selected_elem.show();
			selected_elem.removeClass('not-active');
			$(this).removeClass('convert')
		}else{
			selected_elem.hide();
			selected_elem.addClass('not-active');
			$(this).addClass('convert')
		}

	});

	$(".reg-ticket-section-content .options label").on('click', function(){
		$('.reg-ticket-section-wrapper .member_data').each(function(){
			$(this).show();
		});
		$('.reg-ticket-section-wrapper .non_member_data').each(function(){
			$(this).hide();
		});
	});

	$(".reg-ticket-section-content > label").on('click', function(){
		if(!$(this).find('input[name="be_a_member"]').is(':checked')){
			$('.reg-ticket-section-wrapper .member_data').each(function(){
				$(this).hide();
			});
			$('.reg-ticket-section-wrapper .non_member_data').each(function(){
				$(this).show();
			});
		}
	});
	
	$(".reg-ticket-section-content input[type='checkbox']").on('click', function(){
		$('.js_package_fee').find('span').text('0');
		$('.cart-table').find('tbody').find('tr').each(function(){
			id = $(this).attr('id');
			$('#'+id+'').remove();
		});
		$('.no-of-persons').find('.weight').find('input').val(0);
		$('.js_payable_now').find('span').text('0');
		$('.js_total_payable').find('span').text('0');
	});
	
});

// rida testing