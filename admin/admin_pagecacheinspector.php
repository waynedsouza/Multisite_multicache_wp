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

function multicache_page_cache_inspector_menu()
{
	$pageCacheobject = MulticacheFactory::getPageCacheObject();
	$items = $pageCacheobject->getData();//should generate the total before calling stats
	$stat = $pageCacheobject->getHitStats();
	$pagination = 	$pageCacheobject->getPaginationObject();
	$listOrder  = $pagination['order'];
	$listDirn   = $pagination['direction'];
	
	$big = 9999;
	$args = array(
			'base'               => str_replace(array('#038;','&&'),array('&','&'),str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) )),
			'format'             => '?page=%#%',
			'total'              => $pagination['total_pages'],
			'current'            => $pagination['current_page'],
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
	
	
	?><div class="wrap container-fluid">
	<div class="container-fluid content-fluid alert alert-info">
	<h3 class="small">Stat</h3>

					<div class="col-md-3 inline">Pages in Memcache : <?php echo $stat->total;?></div>
					<div class="col-md-3 inline">Get Rate : <?php echo number_format($stat->getrate * 100, 1); ?>% </div>
					<div class="col-md-3 inline">Delete Rate : <?php echo number_format($stat->deleterate * 100, 1);?>% </div>
					<div class="col-md-3 inline">Timestamp : <?php echo $stat->timestamp;?></div>
					<div class="col-md-2 inline">Uptime : <?php echo gmdate('h:i:s', $stat->uptime);?></div>
					<div class="col-md-2 inline">Get hits : <?php echo number_format($stat->get_hits, 0);?></div>
					<div class="col-md-2 inline">Get Misses : <?php echo number_format($stat->get_misses, 0);?></div>
					<div class="col-md-2 inline">Delete hits :  <?php echo number_format($stat->delete_hits, 0);?></div>
					<div class="col-md-2 inline">Delete Misses : <?php echo number_format($stat->delete_misses, 0);?></div>
					<div class="col-md-2 inline">Current items : <?php echo number_format($stat->curr_items, 0);?></div>

					<p class="small" style="margin-top: 1em;"></p>

				</div>
				
				<form id="multicache_pagecacheinspector_form" class="col-md-12 multicache_form" action="options.php" method="post">
				<?php wp_nonce_field('multicache_pagecacheinspector_admin','multicache_pagecacheinspector_nonce');wp_nonce_field('update-options');?><input name="action" value="update" type="hidden">
				<input type="submit"	value="<?php  esc_attr_e('Delete' , 'multicache-plugin');?>" name="delete_pci" class="button button-secondary " />
				<div id="filters">
<!--  type -->
<?php  echo makeSelectButtonTextual('filter_pcitype_fl', 'filter_pcitype_fl', $pagination['cache_standard'] , false, '',  array(
        1 => __('Standard ','multicache-plugin'),
        2 => __('NonStandard ','multicache-plugin'),
      
    ),null,' advsimres', __('filter a url type ','multicache-plugin'));?></div>
				<table class="widefat table table-striped">
				<thead>	
				<tr><th width="1%" class="hidden-phone">
				<input type="checkbox" id="cache_clear_checkall" name="cache_clear_checkall" value="" class="hasTooltip" title="Check All"></th>
				<th width="1%" class="nowrap center hidden-phone"><a href="#"  class="hasTooltip sortable" tag="id" title="Click to sort by this column">ID</a></th>
				<th width="8%" class="center nowrap"><a href="#"  class="hasTooltip sortable" tag="url" title="Click to sort by this column">Url</a></th>
				<th width="8%" class="center nowrap"><a href="#"  class="hasTooltip sortable" tag="views" title="Click to sort by this column">Views <span class="icon-arrow-down-3"></span></a></th>
				<th width="5%" class="nowrap center"><a href="#"  class="hasTooltip sortable" tag="cacheid" title="Click to sort by this column">Cache ID</a></th>
				<th width="5%" class="nowrap center"><a href="#"  class="hasTooltip sortable" tag="type" title="Click to sort by this column">Type</a></th>
                </tr>
                
				</thead>
				<tbody>
				<?php 
				if(!empty($items)):
				foreach($items As $key => $item):
					?><tr class="row<?php
            echo $i % 2;
            ?>">
        <td class="center hidden-phone">
<input type="checkbox" id="cb<?php echo $key;?>" class="cache_box_check" name="cid[]" value="<?php echo $item['cache_id']?>" ></td>
<td class="  hidden-phone has-context"><?php echo $item['id']?></td>
<?php add_thickbox(); ?><td class="nowrap hascontext "><a href="<?php echo plugins_url('simcontrol/cacheview.php?c_id='.$item['cache_id'].'&width=1100&height=550' ,dirname(__FILE__));?>" class=" thickbox"  title="Render page from cache"> <?php echo $item['url'];?></a>
</td>
<td class="nowrap hascontext "><?php echo $item['views'];?></td>
<td class="nowrap hascontext "><a href="<?php echo plugins_url('simcontrol/cachecode.php?c_id='.$item['cache_id'].'&width=1100&height=550' ,dirname(__FILE__));?>" class=" thickbox"  title="View item as stored in cache"><?php echo $item['cache_id'];?></a>
</td>
<td class="nowrap hascontext "><?php echo $item['type'];?></td>
<?php 
				endforeach;
				endif;
?></tr>
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
	
				
				</table>
<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;?>" />
<input type="hidden" name="screen_name" value="pci" />
<input id="actionType" value="" type="hidden">
				
				</form>
	</div><?php 
}