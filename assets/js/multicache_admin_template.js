 jQuery(document).ready(function (){
        jQuery("#tabs").tabs();
        jQuery('.chzn-chosen:not(.col-md-1,.tweak_selectors)').chosen({"disable_search_threshold":10,"allow_single_deselect":true,"placeholder_text_multiple":"Select some options","placeholder_text_single":"Select an option","no_results_text":"No results match","width":"153px"});
        jQuery('.chzn-chosen.col-md-1').chosen({"disable_search_threshold":10,"allow_single_deselect":true,"placeholder_text_multiple":"Select some options","placeholder_text_single":"Select an option","no_results_text":"No results match","width":"63px"});
        jQuery('.chzn-chosen.tweak_selectors').chosen({"disable_search_threshold":10,"allow_single_deselect":true,"placeholder_text_multiple":"Select some options","placeholder_text_single":"Select an option","no_results_text":"No results match","width":"100px"});
jQuery('#multicache_config_options_googlestartdate,#multicache_config_options_googleenddate,#datepicker_adv_res_from,#datepicker_adv_res_to').datepicker({
    showOn: "button",
    //buttonImage: "../images/calendar.gif",
    //buttonImageOnly: true,
    buttonText: "<span class='glyphicon glyphicon-calendar'></span>",
    changeMonth: true,
    changeYear: true,
    dateFormat:'yy-mm-dd'
  });
//minicolors test
jQuery('.minicolors').each(function() {
	jQuery(this).minicolors({
		control: jQuery(this).attr('data-control') || 'hue',
		/*position: jQuery(this).attr('data-position') || 'right',*/
		theme: 'bootstrap'
	});
});
//end minicolors test
jQuery('.advsimres').change(function(e){
	this.form.submit();
	});
jQuery('div.message,div.woocommerce-message').not('.multicache_message').hide();

//loadsection selector reset_loadsection
jQuery('.multicache_loadsection').change(function(e , f) {
if(jQuery('#reset_loadsection').hasClass('hidden')){
jQuery('#reset_loadsection').removeClass('hidden');
}
jQuery('#reset_loadsection').hasClass('hidden').removeClass('hidden');
});
//start css loadsection reset
jQuery('.multicache_cssloadsection').change(function(e , f) {
if(jQuery('#reset_cssloadsection').hasClass('hidden')){
jQuery('#reset_cssloadsection').removeClass('hidden');
}
jQuery('#reset_cssloadsection').hasClass('hidden').removeClass('hidden');
});
//end css loadsection reset
//loadsection reset opertaion
jQuery('#reset_loadsection').on('click', function(event){
event.preventDefault();
jQuery('.multicache_loadsection').val("0").trigger("chosen:updated");
jQuery('#reset_loadsection').addClass("hidden");
});


//cssloadsection reset opertaion
jQuery('#reset_cssloadsection').on('click', function(event){
event.preventDefault();

// jQuery('.com_multicache_loadsection').chosen();
jQuery('.multicache_cssloadsection').val("0").trigger("chosen:updated");//later versions use chosen:updated
jQuery('#reset_cssloadsection').addClass("hidden");
});
//start grouping selector
jQuery('.grouping_selector select').change(function(e , f) {
var p =  jQuery( this ).val();
var q =  this.id;
if(p == 1){
var s =  jQuery(this).parent().siblings('.group_number_selector').fadeIn();//nextUntil('.delaytype_selector');
}
if(p == 0){
	jQuery(this).parent().siblings('.group_number_selector').fadeOut();
}
});
//end grouping selector
//cache clear form submission
jQuery('#cache_clear_checkall').change(function(e){
    e.preventDefault();
    if(this.checked){
    jQuery('input.cache_box_check').prop('checked',true);
    }
    else
    {
     jQuery('input.cache_box_check').prop('checked',false);
    }
});
jQuery('input[name="delete_cache"],input[name="delete_urls"],input[name="delete_advres"]').on('click',function(e){
e.preventDefault();   

    var len = jQuery('input.cache_box_check:checked').length;
   if(len == 0)
   {
       alert('Please select items to be deleted');
   }
    else{
    	var name_action = this.name;
    	jQuery('input#actionType').attr('name',name_action );
    	jQuery('input#actionType').val(1);
        //alert(this.form.id);
        this.form.submit();
    }
    
});
jQuery('a.sortable').on('click',function(e){
	e.preventDefault();
	//jQuery(this).attr('tag')
	var cur_order = jQuery('input[name="filter_order"]').val();
		
		if(jQuery(this).attr('tag') != cur_order)
			{
			jQuery('input[name="filter_order"]').val(jQuery(this).attr('tag'));
			jQuery('input[name="filter_order_Dir"]').val('asc'); 
			jQuery('form').submit();
			}
		else
			{
			var dir = jQuery('input[name="filter_order_Dir"]').val();
			if(dir == 'asc')
				{
				jQuery('input[name="filter_order_Dir"]').val('desc');
				jQuery('form').submit();
				}
			else
				{
				jQuery('input[name="filter_order_Dir"]').val('asc');
				jQuery('form').submit();
				}
			}
	
});
//end cache clear form submission
//is this additional required
//form start
//
//start
/*
jQuery('input#multicache_google_auth_button,input#multicache_scrape_template_button,input#multicache_scrape_css_button').on('click',function(e){
e.preventDefault();
//console.log("logging click url"+ );
//jQuery('form#multicache_config_form').attr('action' , 'multicache_google_authenticate.php').submit();
jQuery('#submission_message').text('Submitting..');
var url = jQuery(this).attr('u');
var form = jQuery("form#multicache_config_form");
var success = 'Successfully saved';
var failure = ' failed loading ';
//var saved = submitMulticacheNA(form,  'options.php' , success, failure);//equivalent to saving
var executed = submitMulticacheNA(form,  url , success, failure);
console.log('submission of form to url ' + url + ' resulted in saved message ' + saved + ' executed message ' + executed);
});*/
//stop
/*
jQuery('input[value="Save"]').on('click',function(e){
	jQuery('#submission_message').text('');
	var form = jQuery("form#multicache_config_form");
	e.preventDefault();
	var u = 'options.php';
	jQuery.ajax({
	           type: "POST",
	           url: u,
	           data: jQuery(form).serialize(), // serializes the form's elements.
	           success: function(data)
	           {
	        
	           jQuery('#submission_message').text('Successfully saved');
	            }
	         });
	         

	});*/
//form end
var startdate = jQuery('#multicache_config_options_googlestartdate').val();
var enddate =  jQuery('#multicache_config_options_googleenddate').val();
jQuery('#multicache_config_options_googlestartdate').datepicker( "setDate", startdate ); 
jQuery('#multicache_config_options_googleenddate').datepicker( "setDate", enddate ); 
//jQuery('#datepicker_adv_res_from,#datepicker_adv_res_to').datepicker();
//critical checks here
        //set the active class records straight
        //first for fragment based url entries
        var query = location.href.split('#');
        if(query[1] )
        {
        var fragment = '#' + query[1];
        removeActive();
        var z = jQuery('a[href="'+ fragment +'"]').parent().addClass('active');
        jQuery("div"+ fragment).addClass('active');
        }
       
        jQuery(document).tooltip({
            position: {
                my: "left top",
                at: "right+5 top-5"
              }
            });
      //second part is not required as it works by default
      jQuery("fieldset.radio.btn-group-multi").on('change',function(event){
        
        	
        	var cur_val = jQuery( this ).find('input:checked').val();
        	var cur_id = jQuery( this ).find('input:checked').attr('id');
        	jQuery(this).find('label.btn-success').removeClass('btn-success').removeClass('active');
        	jQuery(this).find('label[for='+cur_id +']').addClass('btn-success').addClass('active');
        	
        	
        });
       
        //radio buttons
        jQuery("fieldset.radio.btn-group > label").on('click',function(event){
            event.preventDefault;
            //part1 if we click an active link nothing to be done
            if(jQuery(this).hasClass('active'))
            {
                return;
            }
//            alert(jQuery(this).html() );
            //now we assume the label has only class btn
            // we need to understand whether this is a btn-success or btn-danger
            //hence we step out to the parent class and access the btn
            var active_element = jQuery(this).parent().find('label.active');
            var sibcolor_green = active_element.hasClass('btn-success');
            var sibcolor_red = active_element.hasClass('btn-danger');
            
            if(sibcolor_red)
            {
                
                //we need to add back btn-success
             
                active_element.removeClass('btn-danger').removeClass('active');
                jQuery(this).addClass('btn-success').addClass('active');
                
                
            }
            else if(sibcolor_green)
            {
                active_element.removeClass('btn-success').removeClass('active');
                jQuery(this).addClass('btn-danger').addClass('active');
                
            }
            
           
        });
        
var z = jQuery("input:radio[name='multicache_config_options[gtmetrix_allow_simulation]']:checked").val();
if(z == 0) 
{
	var u = jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]').parent().parent();
    u.hide();
}
 
jQuery('fieldset#multicache_config_options\\[gtmetrix_allow_simulation\\]').change(function() {
var z = jQuery("input:radio[name='multicache_config_options[gtmetrix_allow_simulation]']:checked").val();
var u = jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]').parent().parent();
var s = jQuery('input:radio[name="multicache_config_options[jssimulation_parse]"]').parent().parent().parent();
var sim = jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]:checked').val();
if(z == 0)
{
u.fadeOut(1400);
s.fadeOut(1400)
}
else if(z == 1)
{
u.fadeIn(1400);
if(sim == 1)
	{
	s.fadeIn(1400);
	}
}
 });
 
var checkbox = jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]:checked').val();
if(checkbox != 1){

	jQuery('input:radio[name="multicache_config_options[jssimulation_parse]"]').parent().parent().parent().hide();
	}
jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]').change(function() {
	if(jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]:checked').val() == 1)
		{
		jQuery('input:radio[name="multicache_config_options[jssimulation_parse]"]').parent().parent().parent().show(1000);
		}
	else
		{
		jQuery('input:radio[name="multicache_config_options[jssimulation_parse]"]').parent().parent().parent().hide(1000);
		}
});
if(jQuery("input:radio[name='multicache_config_options[gtmetrix_allow_simulation]']:checked").val() === '0')
	{
	jQuery('input:checkbox[name="multicache_config_options[simulation_advanced]"]').parent().parent().hide();
	jQuery('input:radio[name="multicache_config_options[jssimulation_parse]"]').parent().parent().parent().hide(1000);
	}
//cartmode start

jQuery('select[name="multicache_config_options[multicachedistribution]"').change(function() {
if(jQuery(this).val() === '0')
{
jQuery('fieldset#multicache_config_options\\[cartmode\\]').parent().parent().parent().parent().fadeIn(1400).siblings('h3').fadeIn(1400);
}
else
{
jQuery('fieldset#multicache_config_options\\[cartmode\\]').parent().parent().parent().parent().fadeOut(1400).siblings('h3').fadeOut(1400);
}

});
//init
if(jQuery('select[name="multicache_config_options[multicachedistribution]"').val() !=='0')
	{
	jQuery('fieldset#multicache_config_options\\[cartmode\\]').parent().parent().parent().parent().hide().siblings('h3').hide(1400);
	}
//cartmode end
//cartmode Adv settings
/*
jQuery('fieldset#multicache_config_options\\[cartmode\\]').change(function(){
	if(jQuery(this).find('input:checked').val() !== '0')
		{
		jQuery('textarea[name="multicache_config_options[cartmodeurlinclude]"]').parent().parent().fadeIn(1400);
		}
	else{
		jQuery('textarea[name="multicache_config_options[cartmodeurlinclude]"]').parent().parent().fadeOut(1400);
	    }
	
});
//init
if(jQuery('fieldset#multicache_config_options\\[cartmode\\]').find('input:checked').val() === '0')
	{
	jQuery('textarea[name="multicache_config_options[cartmodeurlinclude]"]').parent().parent().hide();
	}
	*/
//end cartmode Adv settings
//principle jqueryscope other
/*
jQuery('select[name="multicache_config_options[principle_jquery_scope]"]').change(function(){
		if(jQuery(this).val() ==='2')
			{
			jQuery('input[name="multicache_config_options[principle_jquery_scope_other]"]').parent().parent().fadeIn(1400);
			}
		else
			{
			jQuery('input[name="multicache_config_options[principle_jquery_scope_other]"]').parent().parent().fadeOut(1400);
			}
 });
//init
if(jQuery('select[name="multicache_config_options[principle_jquery_scope]"]').val() !== '2')
	{
	jQuery('input[name="multicache_config_options[principle_jquery_scope_other]"]').parent().parent().hide();
	}
	*/


//end principle jquery scope other

//js excludes
/*
jQuery('fieldset#multicache_config_options\\[js_tweaker_url_include_exclude\\]').change(function(){
if(jQuery(this).find('input:checked').val() === '0')
	{
	jQuery('textarea[name="multicache_config_options[jst_urlinclude]"]').parent().parent().fadeOut(1400);
	}
else
	{
	jQuery('textarea[name="multicache_config_options[jst_urlinclude]"]').parent().parent().fadeIn(1400);
	}
});
*/
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[cartmode\\]') , 
		jQuery('textarea[name="multicache_config_options[cartmodeurlinclude]"]'), '0' ,'0')

toggleHideSeg(jQuery('select[name="multicache_config_options[principle_jquery_scope]"]'),
		 jQuery('input[name="multicache_config_options[principle_jquery_scope_other]"]'),
		 '2' , '2' , 2 , false);
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[js_tweaker_url_include_exclude\\]') , 
		jQuery('textarea[name="multicache_config_options[jst_urlinclude]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[jst_query_include_exclude\\]') , 
		jQuery('textarea[name="multicache_config_options[jst_query_param]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[css_tweaker_url_include_exclude\\]') , 
		jQuery('textarea[name="multicache_config_options[css_urlinclude]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[css_query_include_exclude\\]') , 
		jQuery('textarea[name="multicache_config_options[css_query_param]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[image_lazy_image_selector_include_switch\\]') , 
		jQuery('textarea[name="multicache_config_options[image_lazy_image_selector_include_strings]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[image_lazy_image_selector_exclude_switch\\]') , 
		jQuery('textarea[name="multicache_config_options[image_lazy_image_selector_exclude_strings]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[imagestweaker_url_include_exclude\\]') , 
		jQuery('textarea[name="multicache_config_options[images_urlinclude]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[images_query_include_exclude\\]') , 
		jQuery('textarea[name="multicache_config_options[images_query_param]"]'), '0' ,'0');
toggleHideSeg(jQuery('fieldset#multicache_config_options\\[multicacheconfigtolerance\\]\\[tolerance_switch\\]') , 
		jQuery('input[name="multicache_config_options[multicacheconfigtolerance][danger_tolerance_color]"]'), '0' ,'0',1,true,1);

makeDanger(jQuery('fieldset#multicache_config_options\\[cartmode\\]') , '2' , 'multicache_config_options_cartmode0');
makeDanger(jQuery('fieldset#multicache_config_options\\[cache_comment_invalidation\\]') , '0' , 'multicache_config_options_cache_comment_invalidation2');

makeDanger(jQuery('fieldset#multicache_config_options\\[js_tweaker_url_include_exclude\\]') , '2' , 'multicache_config_options_js_tweaker_url_include_exclude0');
makeDanger(jQuery('fieldset#multicache_config_options\\[jst_query_include_exclude\\]') , '2' , 'multicache_config_options_jst_query_include_exclude0');

makeDanger(jQuery('fieldset#multicache_config_options\\[css_tweaker_url_include_exclude\\]') , '2' , 'multicache_config_options_css_tweaker_url_include_exclude0');
makeDanger(jQuery('fieldset#multicache_config_options\\[css_query_include_exclude\\]') , '2' , 'multicache_config_options_css_query_include_exclude0');

makeDanger(jQuery('fieldset#multicache_config_options\\[imagestweaker_url_include_exclude\\]') , '2' , 'multicache_config_options_imagestweaker_url_include_exclude0');
makeDanger(jQuery('fieldset#multicache_config_options\\[images_query_include_exclude\\]') , '2' , 'multicache_config_options_images_query_include_exclude0');


//library selector
jQuery('.library_selector select').change(function(e , f) {
var p =  jQuery( this ).val();
var q =  this.id;
if(p == 1){
jQuery('.library_selector select').each(function(event  ) {
if(this.id != q){
jQuery(this).parent().addClass('invisible');//fadeOut('slow');
}
});
}
if(p == 0){

jQuery('.library_selector').removeClass('invisible');//fadeIn('slow');
}
});
//delay selector
jQuery('.delay_selector select').change(function(e , f) {
var p =  jQuery( this ).val();
var q =  this.id;
var typeofdelay = jQuery(this).parent().siblings('.delaytype_selector').find('select');
var promise_state = jQuery(this).parent().siblings('.promises_selector').find('select');
if(p == 1){
var s =  jQuery(this).parent().siblings('.delaytype_selector').fadeIn();//nextUntil('.delaytype_selector');
if(typeofdelay.val() == 'onload')
	{
	jQuery(this).parent().siblings('.ident_selector').fadeIn();
	//no promises for onload delays
	jQuery(this).parent().siblings('.promises_selector').fadeOut();
	jQuery(this).parent().siblings('.mau_selector').fadeOut();//nextUntil('.delaytype_selector');
    jQuery(this).parent().siblings('.checktype_selector').fadeOut();
    jQuery(this).parent().siblings('.thenBack_selector').fadeOut();
	}
else{
	jQuery(this).parent().siblings('.ident_selector').fadeOut();
	//no promises for onload delays
	jQuery(this).parent().siblings('.promises_selector').fadeIn();
	if(promise_state.val() == 1)
		{
		multicache_show_promisessub(this);
		}
	else{
		multicache_hide_promisessub(this);
	}
	
}

}
if(p == 0){

jQuery(this).parent().siblings('.delaytype_selector').fadeOut();
jQuery(this).parent().siblings('.ident_selector').fadeOut();
//no promises for onload delays
jQuery(this).parent().siblings('.promises_selector').fadeIn();

if(promise_state.val() == 1)
{
multicache_show_promisessub(this);
}
else{
	multicache_hide_promisessub(this);
}
}
});
//ident selector1.9
//delaytype selector
jQuery('.delaytype_selector select').change(function(e , f) {
var p =  jQuery( this ).val();
console.log('value of p ' + p);
var q =  this.id;
var promise_state = jQuery(this).parent().siblings('.promises_selector').find('select');

if(p == 'onload'){
var s =  jQuery(this).parent().siblings('.ident_selector').fadeIn();//nextUntil('.delaytype_selector');

//no promises for onload delays
jQuery(this).parent().siblings('.promises_selector').fadeOut();
multicache_hide_promisessub(this);
}
if(p !== 'onload'){

jQuery(this).parent().siblings('.ident_selector').fadeOut();
//no promises for onload delays

jQuery(this).parent().siblings('.promises_selector').fadeIn();


if(promise_state.val() == 1)
	{
	multicache_show_promisessub(this);
	}
else
	{
	multicache_hide_promisessub(this);
	}

}
});
//wrap promise selector
jQuery('.promises_selector select').change(function(e , f) {
	var p =  jQuery( this ).val();
	var q =  this.id;
	//var typeofdelay = jQuery(this).parent().siblings('.delaytype_selector').find('select');
	//toggleAsyncUtility(this);
	if(p == 1){
  
		multicache_show_promisessub(this);
	}
	if(p == 0){
		multicache_hide_promisessub(this);
	
	
	}
	});
//mau selector
jQuery('.mau_selector select').change(function(e , f) {
	//var p =  jQuery( this ).val();
	//var q =  this.id;
	//var typeofdelay = jQuery(this).parent().siblings('.delaytype_selector').find('select');
	
	toggleAsyncUtility(this , true);
	
	});
//start delay slector css
 jQuery('.delay_selector_css select').change(function(e , f) {
var p =  jQuery( this ).val();
var q =  this.id;
if(p == 1){
var s =  jQuery(this).parent().siblings('.delaytype_selector_css').fadeIn();//nextUntil('.delaytype_selector');


}
if(p == 0){

jQuery(this).parent().siblings('.delaytype_selector_css').fadeOut();
}
});
 //cdn selector
 jQuery('.cdnalias_selector select').change(function(e , f) {
 var p =  jQuery( this ).val();
 var q =  this.id;
 if(p == 1){
jQuery(this).parents('.content-fluid').find('.cdnurl_selector').removeClass('hidden').parent().removeClass('hidden');



 }
 if(p == 0){

jQuery(this).parents('.content-fluid').find('.cdnurl_selector').addClass('hidden').parent().addClass('hidden');
 }
 });
 //end
 jQuery('.cdnalias_selector_css select').change(function(e , f) {
 var p =  jQuery( this ).val();
 var q =  this.id;
 if(p == 1){
jQuery(this).parents('.content-fluid').find('.cdnurl_selector_css').removeClass('hidden').parent().removeClass('hidden');



 }
 if(p == 0){

jQuery(this).parents('.content-fluid').find('.cdnurl_selector_css').addClass('hidden').parent().addClass('hidden');
 }
 });
/*
jQuery('fieldset#multicache_config_options\\[imagestweaker_url_include_exclude\\]').change(function(){
	if(jQuery(this).find('input:checked').val() === '2')
		{
		jQuery(this).find('input:checked').siblings('label[for="multicache_config_options_imagestweaker_url_include_exclude0"]').removeClass('btn-success').addClass('btn-danger');
		}
	else
	{
		jQuery(this).find('input:checked').siblings('label[for="multicache_config_options_imagestweaker_url_include_exclude0"]').removeClass('btn-danger');
	}
	
});
if(jQuery('fieldset#multicache_config_options\\[imagestweaker_url_include_exclude\\]').find('input:checked').val() === '2')
	{
	jQuery('fieldset#multicache_config_options\\[imagestweaker_url_include_exclude\\]').find('input:checked').siblings('label[for="multicache_config_options_imagestweaker_url_include_exclude0"]').removeClass('btn-success').addClass('btn-danger');
	}
*/
	
//init
//init
/*
if(jQuery('fieldset#multicache_config_options\\[js_tweaker_url_include_exclude\\]').find('input:checked').val() === '0')
	{
	jQuery('textarea[name="multicache_config_options[jst_urlinclude]"]').parent().parent().hide();
	}
*/
//start unit
if(jQuery("input[name='multicache_config_options[conduit_switch]']:checked").val() == 1)
	{
	jQuery("input[name='multicache_config_options[conduit_nonce_name]']").parent().parent().fadeIn(1400);
	}
else{
	jQuery("input[name='multicache_config_options[conduit_nonce_name]']").parent().parent().fadeOut(1400);
}
jQuery("input[name='multicache_config_options[conduit_switch]']").change(function(e){
	var conduit_val = jQuery("input[name='multicache_config_options[conduit_switch]']:checked").val();
	if(conduit_val == 1)
	{
	jQuery("input[name='multicache_config_options[conduit_nonce_name]']").parent().parent().fadeIn(1400);
	}
	else
	{
	jQuery("input[name='multicache_config_options[conduit_nonce_name]']").parent().parent().fadeOut(1400);
	}
});
//end unit

 });
 function toggleAsyncUtility(t , a )
 {
	 a = typeof a === 'undefined' ? false: a;
	 if(a)
		 {
		 var mau = jQuery(t); 
		 }
	 else{
		 var mau = jQuery(t).parent().siblings('.mau_selector').find('select');
	 }
	//since were using siblings we need to construct this separately when testing the mau_selector as its self not siblings
	 
	
	if(mau.val() == 1)
		{
		jQuery(t).parent().siblings('.mautime_selector').fadeIn();
		}
	else{
		jQuery(t).parent().siblings('.mautime_selector').fadeOut();
	}
 }
 function multicache_show_promisessub(t)
 {
	
	jQuery(t).parent().siblings('.mau_selector').fadeIn();//nextUntil('.delaytype_selector');
	jQuery(t).parent().siblings('.checktype_selector').fadeIn();
	jQuery(t).parent().siblings('.thenBack_selector').fadeIn();
	toggleAsyncUtility(t);
 }
 function multicache_hide_promisessub(t)
 {
	 
	 jQuery(t).parent().siblings('.mau_selector').fadeOut();
	 jQuery(t).parent().siblings('.checktype_selector').fadeOut();
	 jQuery(t).parent().siblings('.thenBack_selector').fadeOut(); 
	 jQuery(t).parent().siblings('.mautime_selector').fadeOut();
 }
 
 function makeDanger(fieldname , val ,forval )
 {
	 fieldname.change(function(){
		 if(jQuery(this).find('input:checked').val() === val)
			 {
			 jQuery(this).find('input:checked').siblings('label[for="'+ forval + '"]').removeClass('btn-success').addClass('btn-danger');
			 }
		 else
			{
			 jQuery(this).find('input:checked').siblings('label[for="' + forval + '"]').removeClass('btn-danger');
			}
	 });
	 if(fieldname.find('input:checked').val() === val)
		{
		 fieldname.find('input:checked').siblings('label[for="'+ forval + '"]').removeClass('btn-success').addClass('btn-danger');
		}
 }
 
 


function toggleHideSeg(fieldsetchangeid , hidename , hideval , initval , type  , logic , nextall , parent_until)
{
	console.log(fieldsetchangeid );
	type = typeof type !== 'undefined' ? type : 1;
	logic = typeof logic !== 'undefined' ? logic : true;
	nextall = typeof nextall !== 'undefined' ? 1 : 0;
	parent_until = typeof parent_until !== 'undefined' ? parent_until : 'tr';
	fieldsetchangeid.change(function(){
		var ifclause = (type === 1) ? jQuery(this).find('input:checked').val() : jQuery(this).val();
		if(logic === true)
			{
		        if(ifclause === hideval)
		     	{
		            sub = hidename.parentsUntil(parent_until).parent().fadeOut(1400);
		        	nextall === 1? sub.nextAll().fadeOut(1400):null;
		        			        	
		    	}
		        else
			    {
		        	sub = hidename.parentsUntil(parent_until).parent().fadeIn(1400);
		        	nextall === 1? sub.fadeIn(1400).nextAll().fadeIn(1400): null;
		        
			    }
			}
		else
			{
			   if(ifclause === hideval)
			     {
				   sub = hidename.parentsUntil(parent_until).parent().fadeIn(1400);
				   nextall === 1? sub.nextAll().fadeIn(1400) :null;
				  
			     }
		      else
		     	{
		    	  sub = hidename.parentsUntil(parent_until).parent().fadeOut(1400);
		    	  nextall === 1? sub.nextAll().fadeOut(1400) : null;
		    	  
		    	}
			}
	});
	var ifclause = (type === 1) ? fieldsetchangeid.find('input:checked').val() : fieldsetchangeid.val();
	if(logic === true)
		{
	       if(ifclause === hideval)
	     	{
	    	   sub = hidename.parentsUntil(parent_until).parent().hide();
	    	   nextall === 1? sub.nextAll().hide() : null;
	    	 
	     	}
		}
	  else
		{
		    if(ifclause !== hideval)
		    {
		    	sub = hidename.parentsUntil(parent_until).parent().hide();
		    	nextall === 1? sub.nextAll().hide() : null;	
		    }
		}
}


 
 function submitMulticache(form , url , success, failure)
 {
	 jQuery.ajax({
		   type: "POST",
		   url: url,
		   data: jQuery(form).serialize(), // serializes the form's elements.
		   success: function(data)
		   {

		   jQuery('#submission_message').text(success + data);
		   return $data;
		    }
		 

		 }).fail(function(e,y,t){
			 console.log(failure + e + y + t);
		 });
 }
 function submitMulticacheNA(form , url , success, failure)
 {
	 jQuery(form).attr('action', url).submit();
	 /*
	 jQuery.ajax({
		   type: "POST",
		   url: url,
		   data: jQuery(form).serialize(), // serializes the form's elements.
		   success: function(data)
		   {

		   jQuery('#submission_message').text(success + data);
		   return $data;
		    }
		 

		 }).fail(function(e,y,t){
			 console.log(failure + e + y + t);
		 });
		 */
 }
 function removeActive(){

            jQuery('li').removeClass('active');
            jQuery('div').removeClass('active');


            }
 /*
 function printObject(o) {
	  var out = '';
	  for (var p in o) {
	    out += p + ': ' + o[p] + '\n';
	  }
	  console.log(out);
	}
 */