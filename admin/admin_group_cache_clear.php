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
// stems from add settings section
// Draw the section header
defined('_MULTICACHEWP_EXEC') or die();


function multicache_network_cache_clear_menu()
{
	$cache_object = MulticacheFactory::getCacheAdmin();
	$obj = $cache_object->getCacheAdminObject();	
	
	$pagination = 	$obj->pagination;
	///$listOrder  = $pagination['order'];
	//$listDirn   = $pagination['direction'];
	
	$big = 9999;
	$args = array(
			'base'               => str_replace(array('#038;','&&'),array('&','&'),str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) )),
			'format'             => '?page=%#%',
			'total'              => $pagination->total_pages ,
			'current'            => $pagination->current_page,
			'show_all'           => false,
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
	
	
	 

	?>
	<div class="wrap container-fluid">
	<div class="container-fluid content-fluid alert alert-info">
<div class="row-fluid">	
<h3 class="small"><?php _e('Stat','multicache-plugin');?></h3>
<div class="col-md-3 inline"><?php _e('Cache size','multicache-plugin');?> :<?php echo MulticacheHelper::convertBytes($obj->hitstats->filesize * 1024);?>  </div>
<div class="col-md-3 inline"><?php _e('Get Rate','multicache-plugin');?> : <?php echo number_format($obj->hitstats->getrate * 100, 1);?>% </div>
<div class="col-md-3 inline"><?php _e('Delete Rate','multicache-plugin');?> : <?php echo number_format($obj->hitstats->deleterate * 100, 1);?>% </div>
<div class="col-md-3 inline"><?php _e('Timestamp','multicache-plugin');?> : <?php echo $obj->hitstats->timestamp;?></div>
<div class="col-md-2 inline"><?php _e('Uptime','multicache-plugin');?> : <?php echo gmdate('h:i:s', $obj->hitstats->uptime);?></div>
<div class="col-md-2 inline"><?php _e('Get hits','multicache-plugin');?> : <?php echo number_format($obj->hitstats->get_hits, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Get Misses','multicache-plugin');?> :<?php echo number_format($obj->hitstats->get_misses, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Delete hits','multicache-plugin');?> :<?php echo number_format($obj->hitstats->delete_hits, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Delete Misses','multicache-plugin');?> :<?php echo number_format($obj->hitstats->delete_misses, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Current items','multicache-plugin');?> : <?php echo number_format($obj->hitstats->curr_items, 0);?> </div>
<p class="small" style="margin-top: 1em;"></p>
</div>
	</div><!-- closes container fluid alert -->

<form id="multicache_metworkcacheclear_form" class="col-md-12" action="edit.php?action=multicache_network_cache_management" method="post">
<?php wp_nonce_field('multicache_cache_admin','multicache_cache_control_nonce');wp_nonce_field('update-options')?>
<div class="row-fluid">	
<input name="action" value="update" type="hidden">
<input type="submit"
			value="<?php  esc_attr_e('Delete' , 'multicache-plugin');?>" name="delete_cache"
			class="button button-primary  col-md-1" />
			<!--makeSelectButtonNumeric($option, $opvar, $get_var = '20', $required = false, $title_tag = '', $start = '0', $stop = '100', $interval = '1', $labels = null, $third_param = null , $class_adds = null ) -->
			<div id="filter_cache_type" class="col-md-2">
			<?php echo makeSelectButtonNumeric('filter_cache_type','filter_cache_type',$obj->cache_type, false,'',0,2,1,array( 0 =>'', 1 => 'filecache' , 2 => 'Memcache'),null,' advsimres','Cache type..');?>
			</div>
			<?php if(isset($obj) && !empty($obj->back)):?>
			<div class="col-md-1"><a href="admin.php?page=multicache-network-cache-clear-menu&back=<?php echo $obj->back['name']?>" class="glyphicon glyphicon-arrow-left">  back</a></div >
			<?php endif;?>
			</div>
<table class="table table-striped">
<!-- table head -->
<thead>
<tr>
<th class="col-md-1"><input id="cache_clear_checkall" type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title=""  title="Check All"></th>
<th class="title nowrap col-md-8"><a href="#"  class="hasTooltip" title="<strong>Cache Group</strong><br />Click to sort by this column">Cache Group</a></th>
<th  class="center nowrap col-md-2"><a href="#"  class="hasTooltip" title="<strong>Number of Files</strong><br />Click to sort by this column">Number of Files <span class="icon-arrow-down-3"></span></a></th>
<th class="center col-md-1"><a href="#"  class="hasTooltip"title="<strong>Size</strong><br />Click to sort by this column">Size</a></th>
</tr>
</thead>
<tbody>
<?php 
if(isset($obj->_data))
{
foreach($obj->_data As $key => $val)
{
	$id = "cb".$key;
	$class = $key % 2;
	$value_group  = $val->group; 
	?>
	<tr class="row<?php echo esc_attr($class);?>">
	<td><input type="checkbox" id="<?php echo esc_attr($id);?>" name="cid[]" value="<?php echo esc_attr($value_group);?>" class="cache_box_check" ></td>
	<td><strong><a href="admin.php?page=multicache-network-cache-clear-menu&subfolder=<?php echo $value_group;?>" ><?php echo $value_group ;?></a></strong></td>
	<td class="center">	<?php echo $val->count;?>	</td>
	<td class="center">	<?php echo MulticacheHelper::convertBytes($val->size*1024);?></td>
	</tr>
	<?php 
}
}
?>
</tbody>
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
<!-- end table head -->
</table>
<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;?>" />
<input type="hidden" name="screen_name" value="gcc" />
<input id="actionType" value="" type="hidden">
</form>


		
	</div>
	<?php 
}

function multicache_group_cache_clear_menu()
{
	$cache_object = MulticacheFactory::getCacheAdmin();
	$obj = $cache_object->getCacheAdminObject();
	$pagination = 	$obj->pagination;
	///$listOrder  = $pagination['order'];
	//$listDirn   = $pagination['direction'];
	
	$big = 9999;
	$args = array(
			'base'               => str_replace(array('#038;','&&'),array('&','&'),str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) )),
			'format'             => '?page=%#%',
			'total'              => $pagination->total_pages ,
			'current'            => $pagination->current_page,
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
	
	
	?>
	<div class="wrap container-fluid">
	<div class="container-fluid content-fluid alert alert-info">
<div class="row-fluid">	
<h3 class="small"><?php _e('Stat','multicache-plugin');?></h3>
<div class="col-md-3 inline"><?php _e('Cache size','multicache-plugin');?> :<?php echo MulticacheHelper::convertBytes($obj->hitstats->filesize * 1024);?>  </div>
<div class="col-md-3 inline"><?php _e('Get Rate','multicache-plugin');?> : <?php echo number_format($obj->hitstats->getrate * 100, 1);?>% </div>
<div class="col-md-3 inline"><?php _e('Delete Rate','multicache-plugin');?> : <?php echo number_format($obj->hitstats->deleterate * 100, 1);?>% </div>
<div class="col-md-3 inline"><?php _e('Timestamp','multicache-plugin');?> : <?php echo $obj->hitstats->timestamp;?></div>
<div class="col-md-2 inline"><?php _e('Uptime','multicache-plugin');?> : <?php echo gmdate('h:i:s', $obj->hitstats->uptime);?></div>
<div class="col-md-2 inline"><?php _e('Get hits','multicache-plugin');?> : <?php echo number_format($obj->hitstats->get_hits, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Get Misses','multicache-plugin');?> :<?php echo number_format($obj->hitstats->get_misses, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Delete hits','multicache-plugin');?> :<?php echo number_format($obj->hitstats->delete_hits, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Delete Misses','multicache-plugin');?> :<?php echo number_format($obj->hitstats->delete_misses, 0);?> </div>
<div class="col-md-2 inline"><?php _e('Current items','multicache-plugin');?> : <?php echo number_format($obj->hitstats->curr_items, 0);?> </div>
<p class="small" style="margin-top: 1em;"></p>
</div>
	</div><!-- closes container fluid alert -->

<form id="multicache_groupcacheclear_form" class="col-md-12" action="options.php" method="post">
<?php wp_nonce_field('multicache_cache_admin','multicache_cache_control_nonce');wp_nonce_field('update-options')?>
<div class="row-fluid">	
<input name="action" value="update" type="hidden">
<input type="submit"
			value="<?php  esc_attr_e('Delete' , 'multicache-plugin');?>" name="delete_cache"
			class="button button-primary  col-md-1" />
			<!--makeSelectButtonNumeric($option, $opvar, $get_var = '20', $required = false, $title_tag = '', $start = '0', $stop = '100', $interval = '1', $labels = null, $third_param = null , $class_adds = null ) -->
			<div id="filter_cache_type" class="col-md-3">
			<?php echo makeSelectButtonNumeric('filter_cache_type','filter_cache_type',$obj->cache_type, false,'',0,2,1,array( 0 =>'', 1 => 'filecache' , 2 => 'Memcache'),null,' advsimres','Cache type..');?>
			</div></div>
<table class="table table-striped">
<!-- table head -->
<thead>
<tr>
<th class="col-md-1"><input id="cache_clear_checkall" type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title=""  title="Check All"></th>
<th class="title nowrap col-md-8"><a href="#"  class="hasTooltip" title="<strong>Cache Group</strong><br />Click to sort by this column">Cache Group</a></th>
<th  class="center nowrap col-md-2"><a href="#"  class="hasTooltip" title="<strong>Number of Files</strong><br />Click to sort by this column">Number of Files <span class="icon-arrow-down-3"></span></a></th>
<th class="center col-md-1"><a href="#"  class="hasTooltip"title="<strong>Size</strong><br />Click to sort by this column">Size</a></th>
</tr>
</thead>
<tbody>
<?php 
if(isset($obj->_data))
{
foreach($obj->_data As $key => $val)
{
	$id = "cb".$key;
	$class = $key % 2;
	$value_group  = $val->group; 
	?>
	<tr class="row<?php echo esc_attr($class);?>">
	<td><input type="checkbox" id="<?php echo esc_attr($id);?>" name="cid[]" value="<?php echo esc_attr($value_group);?>" class="cache_box_check" ></td>
	<td><strong><?php echo $value_group ;?></strong></td>
	<td class="center">	<?php echo $val->count;?>	</td>
	<td class="center">	<?php echo MulticacheHelper::convertBytes($val->size*1024);?></td>
	</tr>
	<?php 
}
}
?>
</tbody>
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
<!-- end table head -->
</table>
<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;?>" />
<input type="hidden" name="screen_name" value="gcc" />
<input id="actionType" value="" type="hidden">
</form>


		
	</div>
	<?php 
}