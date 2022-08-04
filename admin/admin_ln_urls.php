<?php Author URI: http://onlinemarketingconsultants.in
/**

 * MulticacheWP

 * uri: http://onlinemarketingconsultants.in

 * Description: High Performance fastcache Controller

 * Version: 1.0.0.6

 * Author: Wayne DSouza

 * Author URI: http://multicache.org

 * License: GNU PUBLIC LICENSE see license.txt

 */

// stems from add settings section

// Draw the section header

defined('_MULTICACHEWP_EXEC') or die();





function multicache_urlanalyzer_menu()

{

	$urlobject = MulticacheFactory::getMulticacheUrls();

	$url_stats = $urlobject->getUrlStats();

	$urls = $urlobject->getUrlItems()->items;

	$pagination = $urlobject->getUrlItems()->pagination;

	$listOrder =  $urlobject->getUrlItems()->order;

	$listDirn =   $urlobject->getUrlItems()->direction;

	$big = 9999;

	$args = array(

			'base'               => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),

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

	<div class="row-fluid">	

	<h3 class="small"><?php _e('url stats' , 'multicache-plugin')?></h3>

	<div style="display: inline;">Google urls : <?php echo $url_stats['google'];?></div>

	<div style="display: inline; margin-left: 1.2em;">Manual urls  :  <?php echo $url_stats['manual'];?></div>

    </div>

    </div>

 <form id="multicache_ln_url_form" class="col-md-12 multicache_form" action="options.php" method="post">

 <?php wp_nonce_field('multicache_lnurl_admin','multicache_lnurl_control_nonce');wp_nonce_field('update-options');?>

 <input name="action" value="update" type="hidden">

 <input type="submit"	value="<?php  esc_attr_e('Delete' , 'multicache-plugin');?>" name="delete_urls" class="button button-secondary " />

   <input type="submit"	value="<?php  esc_attr_e('Update Multicache' , 'multicache-plugin');?>" name="update_multicache" class="button button-primary" /> 

<table class="widefat table table-striped">

<thead><tr>

<th width="2%" class="hidden-phone "><input type="checkbox" id="cache_clear_checkall"  name="cache_clear_checkall" value="" class="hasTooltip" title="Check All"></th>

<th width="2%" class="nowrap text-center  hidden-phone "><a href="#"  class="hasTooltip sortable" title="Click to sort by this column"><?php _e('ID','multicache-plugin');?></a></th>

<th  class=" nowrap col-md-4 text-center"><a href="#"  class="hasTooltip sortable" tag="url" title="Click to sort by this column"><?php _e('url','multicache-plugin');?></a></th>

<th  class="text-center  nowrap col-md-2"><a href="#"  class="hasTooltip sortable" tag="views"  title="Click to sort by this column"><?php _e('views','multicache-plugin');?><span class="icon-arrow-down-3"></span></a></th>

<th  class="nowrap text-center  col-md-2"><a href="#"  class="hasTooltip sortable"  tag="freq" title="Click to sort by this column"><?php _e('frequency distribution','multicache-plugin');?></a></th>

<th  class="nowrap text-center  col-md-1"><a href="#"  class="hasTooltip sortable"  tag="log" title="Click to sort by this column"><?php _e('logarithm','multicache-plugin');?></a></th>

<th  class="nowrap text-center  col-md-1"><a href="#"  class="hasTooltip sortable" tag="type"  title="Click to sort by this column"><?php _e('type','multicache-plugin');?></a></th>

<th  class="date text-center  nowrap col-md-1" ><a href="#"  class="hasTooltip sortable" tag="date" title="Click to sort by this column"><?php _e('Date','multicache-plugin');?></a></th>

</tr>

</thead>

<tbody><?php 

foreach($urls As $key => $u):

$alt_class = "row". $key %2;

$id = "cb".$key;

$f_dist = number_format($u->f_dist * 100, 2);

$ln_dist = number_format($u->ln_dist, 2);

?><tr class="$alt_class ">

<td width="2%" class="text-center  hidden-phone">

<input type="checkbox" id="<?php echo $id;?>" name="cid[]" value="<?php echo $u->id;?>" class="cache_box_check " ></td>

<td width="2%" class=" text-center  hidden-phone has-context"><?php echo $u->id;?></td>

<td class="nowrap  col-md-3"><?php echo $u->url;?></td>

<td class="nowrap text-center  col-md-2"><?php echo $u->views;?></td>

<td class="nowrap hascontext text-center  col-md-2"><?php echo $f_dist;?>%</td>

<td class="nowrap hascontext text-center  col-md-2"><?php echo $ln_dist;?></td>

<td class="nowrap text-center  col-md-1"><?php echo $u->type;?></td>

<td class="nowrap text-center  col-md-1"><?php echo $u->created;?></td>

</tr><?php endforeach;?></tbody>

<tfoot>

<tr>

<td colspan="10">

<div class="tablenav">

<div class="tablenav-pages"><?php echo paginate_links( $args );?></div>

</div>

</td>

</tr>

</tfoot>



</table>

<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>" />

<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;?>" />

<input type="hidden" name="screen_name" value="alu" />

<input id="actionType" value="" type="hidden">

</form>

    </div><?php 

}