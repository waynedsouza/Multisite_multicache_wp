<?php
/**
 * MulticacheWP
 * uri: http://onlinemarketingconsultants.in
 * Description: High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */

defined('_MULTICACHEWP_EXEC') or die();


function multicache_network_admin_menu()
{
	$newtork_object = MulticacheFactory::getNetworkAdmin();
	$n_w = $newtork_object->getNetworkManagementObject();
	$pagination = $newtork_object->getPagination();
	
	
	
	/*$adv_sim_obj = MulticacheFactory::getAdvancedSimulation();
	$global_stat = $adv_sim_obj->getglobalStat();
	$testgroup_stat = $adv_sim_obj->getTestGroupStats();
	$result_obj = $adv_sim_obj->getASItems()->items;
	//$pagination = $adv_sim_obj->getASItems()->pagination;*/
	//$listOrder =  $adv_sim_obj->getASItems()->order;
	//$listDirn =  $adv_sim_obj->getASItems()->direction;
	/*$filter_stats = $adv_sim_obj->getFilterableStat();
	$options = get_option('multicache_config_options');
	$tolerance = isset($options['tolerance_params'])? json_decode($options['tolerance_params']):null;*/
	
	$big = 9999;
	$args = array(
			'base'               => str_replace(array('#038;','&&'),array('&','&'),str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) )),
			'format'             => '?page=%#%',
			'total'              => $pagination['total_pages'],
			'current'            => $pagination['current_page'],
			'show_all'           => true,
			'end_size'           => 1,
			'mid_size'           => 2,
			'prev_next'          => True,
			'prev_text'          => __('<< Previous'),
			'next_text'          => __('Next >>'),
			'type'               => 'plain',
			'add_args'           => False,
			'add_fragment'       => '',
			'before_page_number' => '',
			'after_page_number'  => ''
	);
	
	?><div class="wrap container-fluid">

				
				<form id="multicache_networkadmin_form" class="col-md-12 multicache_form" action="edit.php?action=multicache_network_management" method="post">
<?php wp_nonce_field('multicache_networkmanagement_admin','multicache_networkmanagement_nonce');wp_nonce_field('update-options');
?><input name="action" value="update" type="hidden">
<input type="submit"	value="<?php  esc_attr_e('Save' , 'multicache-plugin');?>" name="save_networkmanagement" class="button button-secondary " />

<table class="widefat table table-striped">
<thead>
<tr>
<th width="1%" class="hidden-phone">							
<input type="checkbox" id="cache_clear_checkall" name="cache_clear_checkall" value="" class="hasTooltip" title="Check All"></th>
						<th width="1%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="id" title="Click to sort by this column"><?php _e('ID','multicache-plugin');?><span class="icon-arrow-down-3"></span></a></th>
						<th class="date center nowrap" width="5%">
<a href="#"  class="hasTooltip sortable" tag="siteid" title="Click to sort by this column"><?php _e('Site id','multicache-plugin');?></a></th>
						<th width="5%" class="center nowrap">
<a href="#"  class="hasTooltip sortable" tag="blogid" title="Click to sort by this column"><?php _e('Blog id','multicache-plugin');?></a></th>
						<th width="15%" class="center nowrap">
<a href="#"  class="hasTooltip sortable" tag="url" title="Click to sort by this column"><?php _e('Url','multicache-plugin');?></a></th>
						<th width="15%" class="nowrap center">
<a href="#"  class="hasTooltip sortable" tag="activate" title="Click to sort by this column"><?php _e('Activate','multicache-plugin');?></a></th>
						<th width="15%" class="nowrap center">
<a href="#"  class="hasTooltip sortable" tag="allowCache" title="Click to sort by this column"><?php _e('Allow Caching','multicache-plugin');?></a></th>
						<th width="15%" class="nowrap center">
<a href="#"  class="hasTooltip sortable" tag="allowOptimization" title="Click to sort by this column"><?php _e('Allow Optimization','multicache-plugin');?></a></th>
						

</tr>
</thead>
<tbody>
<?php 
$title_tag_cache = __('Set Blog Caching On/off', 'multicache-plugin');
$info_tag_cache = __('Set Blog Caching On/off. Turning this option on will enable page caching for this blog', 'multicache-plugin');
$title_tag_optimization = __('Set Blog Optimization On/off', 'multicache-plugin');
$info_tag_optimization = __('Set Blog Optimization On/off. Turning this option on will enable performance optimization on this blog', 'multicache-plugin');
$title_tag_activate = __('Activate Multicache', 'multicache-plugin');
$info_tag_activate = __('Activate Multicache for this blog. No access to multicache for deactivated blogs', 'multicache-plugin');

?>
<?php foreach($n_w As $i => $item):
//var_dump($item);

$url = $item['domain'].$item['path'];
$blog_id = $item['blog_id'];
$site_id = $item['site_id'];
$context = "[$site_id][$blog_id]";
//$context = '_s_'.$site_id .'_bl_'.$blog_id;
$option = get_blog_option( $blog_id, 'multicache_network_config' );
if($option === false)
{
	$activate=$cache =$optimize  = 0;
	
}
else{
	$activate = isset($option['activate'])? $option['activate'] : 0;
	$cache = isset($option['cache'])? $option['cache'] : 0;
	$optimize = isset($option['optimize'])? $option['optimize'] : 0;
	}

        ?><tr class="row<?php echo $i %2; echo  $tr_class;?>" style="<?php echo $tr_style;?>">
    
    <td class="center hidden-phone">
<input class="<?php echo  $tr_class;?>cache_box_check" type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $item->id;?>" ></td>
<td class="<?php echo  $tr_class;?>center hidden-phone has-context" style="<?php echo $tr_style;?>"><?php echo $i+1;?></td>
<td class="<?php echo  $tr_class;?>nowrap center " style="<?php echo $tr_style;?>"><?php echo $item['site_id'];?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $item['blog_id'];?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $url;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php

echo makeRadioButton('multicache_network_config'.$context, 'activate', $activate);
?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag_activate;?>"> </span>
</td>
    <td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php 
    echo makeRadioButton('multicache_network_config'.$context, 'cache', $cache);
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag_cache;?>"> </span>
</td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php  

echo makeRadioButton('multicache_network_config'.$context, 'optimize', $optimize);
    
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag_optimization;?>"> </span>
<?php
?></td>

</tr>
<?php 
endforeach;
?></tbody>
<tfoot>
<tr>
<td colspan="10">
<div class="tablenav">
<div class="tablenav-pages">

<?php echo str_replace('&#038;settings-updated=true','',paginate_links( $args ));?></div>
</div>
</td>
</tr>
</tfoot>		
</table>	
<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;?>" />
<input type="hidden" name="screen_name" value="mna" />
<input id="actionType" value="" type="hidden">

</form>
	</div>
	
	<?php 
}