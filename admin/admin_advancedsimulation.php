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


function multicache_simulation_dashboard()
{
	$adv_sim_obj = MulticacheFactory::getAdvancedSimulation();
	$global_stat = $adv_sim_obj->getglobalStat();
	$testgroup_stat = $adv_sim_obj->getTestGroupStats();
	$result_obj = $adv_sim_obj->getASItems()->items;
	$pagination = $adv_sim_obj->getASItems()->pagination;
	$listOrder =  $adv_sim_obj->getASItems()->order;
	$listDirn =  $adv_sim_obj->getASItems()->direction;
	$filter_stats = $adv_sim_obj->getFilterableStat();
	$options = get_option('multicache_config_options');
	$tolerance = isset($options['tolerance_params'])? json_decode($options['tolerance_params']):null;
	
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
					<h3 class="small">
			Stat		</h3>
					<div class="col-md-2 inline">Average : <?php
    echo number_format($global_stat->average_page_load_time / 1000, 2);
    ?>seconds</div>
					<div class="col-md-2 inline">Minimum  : <?php
    echo number_format($global_stat->minimum_page_load_time / 1000, 2);
    ?>seconds</div>
					<div class="col-md-3 inline">Maximum  : <?php
    echo number_format($global_stat->maximum_page_load_time / 1000, 2);
    ?>seconds</div>
					<div class="col-md-3 inline">Standard Deviation  : <?php
    echo number_format($global_stat->standarddeviation_page_load_time / 1000, 4);
    ?>seconds</div>
    
    	
					<div class="col-md-2 inline">Variance : <?php
    echo number_format($global_stat->variance_page_load_time / 1000000, 4);
    ?></div>
    
    <div class="col-md-1 inline">Cycles : <?php
    echo number_format($testgroup_stat->cycles, 0);
    ?></div>
				
					<div class="col-md-3  inline">Remaining tests : <?php
    if (is_string($testgroup_stat->remaining_tests))
    {
        echo $testgroup_stat->remaining_tests;
    }
    elseif (is_numeric($testgroup_stat->remaining_tests))
    {
        echo number_format($testgroup_stat->remaining_tests, 0);
    }
    ?></div>
					<div class="col-md-4   inline">Expected end date : <?php
    echo $testgroup_stat->expected_end_date;
    ?></div>
					<div class="col-md-2 inline">@Tests per day : <?php
    if (is_string($testgroup_stat->testsperday))
    {
        echo $testgroup_stat->testsperday;
    }
    elseif (is_numeric($testgroup_stat->testsperday))
    {
        echo number_format($testgroup_stat->testsperday, 0);
    }
    ?></div>
					<div class="col-md-2 inline">Test mode : <?php
    echo $testgroup_stat->advanced;
    ?></div>

					<p class="small col-md-10" style="margin-top: 1em;"></p>

				</div>
				
				<form id="multicache_advancedsim_form" class="col-md-12 multicache_form" action="options.php" method="post">
<?php wp_nonce_field('multicache_advancedsim_admin','multicache_advancedsimulation_nonce');wp_nonce_field('update-options');
?><input name="action" value="update" type="hidden">
<input type="submit"	value="<?php  esc_attr_e('Delete' , 'multicache-plugin');?>" name="delete_advres" class="button button-secondary " />
<div id="filters">
<!-- results type -->
<?php  echo makeSelectButtonTextual('filter_simflag_rt', 'filter_simflag_rt', $filter_stats->preset->results_type , false, '',  array(
        'simulation' => __('Simulation Results ','multicache-plugin'),
        'fixed' => __('Non Simulation Results ','multicache-plugin'),
      
    ),null,' advsimres', __('Select results type ','multicache-plugin'));?><!-- end results type -->
<!-- Completion -->
<?php  echo makeSelectButtonTextual('filter_simflag_c', 'filter_simflag_c', $filter_stats->preset->complete , false, '',  array(
        1 => __('Show incomplete results','multicache-plugin'),
        2 => __('Show complete Results ','multicache-plugin'),
      
    ),null,' advsimres',__('Select completion ','multicache-plugin'));?><!-- end Completion -->
<!-- Tolerance -->
<?php  echo makeSelectButtonTextual('filter_simflag_tol', 'filter_simflag_tol', $filter_stats->preset->tolerance, false, '',  array(
        
        1 => __('Highlighted as danger','multicache-plugin'),
        2 => __('Highlighted as warning','multicache-plugin'),
		3 => __('Highlighted as success	','multicache-plugin'),
		4 => __('Unhighlighted','multicache-plugin'),
      
    ),null,' advsimres',__('Select tolerance ','multicache-plugin'));?><!-- end Tolerance -->
<!-- Cache Type -->
<?php  echo makeSelectButtonTextual('filter_simflag_ct', 'filter_simflag_ct', $filter_stats->preset->cache_type, false, '',  array(
		1 => __('fastcache','multicache-plugin'),
		2 => __('filecache','multicache-plugin'),
      
    ),null,' advsimres',__('Select by Cache Type ','multicache-plugin'));?><!-- end Cache Type -->
<!-- By Page -->
<?php  echo makeSelectButtonTextual('filter_simflag_page', 'filter_simflag_page', $filter_stats->preset->pages , false, '',  $filter_stats->pages ,null,' advsimres',__('Select by Page ','multicache-plugin'));?><!-- end page -->
<!-- Precache factor -->

<?php  echo makeSelectButtonTextual('filter_simflag_precache', 'filter_simflag_precache', $filter_stats->preset->precache, false, '',  $filter_stats->precache ,null,' advsimres',__('Select by precache ','multicache-plugin'));?><!-- end precache factor -->
 <!-- Cache Compression  factor -->
<?php 
 echo makeSelectButtonTextual('filter_simflag_ccomp', 'filter_simflag_ccomp', $filter_stats->preset->ccomp , false, '',  $filter_stats->ccomp ,null,' advsimres',__('Select by fastLZ ','multicache-plugin'));?><!-- end Cache Compression factor -->
 
 <!-- Operation Mode -->
<?php  echo makeSelectButtonTextual('filter_simflag_opmode', 'filter_simflag_opmode', $filter_stats->preset->distribution , false, '',  $filter_stats->distribution ,null,' advsimres',__('Select by mode ','multicache-plugin'));?><!-- end Operation Mode -->

    <!-- datepicker -->
    <?php 
    echo makeDateButton('datepicker', 'adv_res_from', $filter_stats->preset->datefrom , 'Select a from date' , ' advsimres');
    echo makeDateButton('datepicker', 'adv_res_to', $filter_stats->preset->dateto , 'Select a to date', ' advsimres');
    ?><!-- end datepicker --></div>
<table class="widefat table table-striped">
<thead>
<tr>
<th width="1%" class="hidden-phone">							
<input type="checkbox" id="cache_clear_checkall" name="cache_clear_checkall" value="" class="hasTooltip" title="Check All"></th>
						<th width="1%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="id" title="Click to sort by this column"><?php _e('ID','multicache-plugin');?><span class="icon-arrow-down-3"></span></a></th>
						<th class="date center nowrap" width="5%">
<a href="#"  class="hasTooltip sortable" tag="date" title="Click to sort by this column"><?php _e('Date','multicache-plugin');?></a></th>
						<th width="8%" class="center nowrap">
<a href="#"  class="hasTooltip sortable" tag="plt" title="Click to sort by this column"><?php _e('Page Load Time','multicache-plugin');?><sub><sub>seconds</sub></sub></a></th>
						<th width="8%" class="center nowrap">
<a href="#"  class="hasTooltip sortable" tag="hlt" title="Click to sort by this column"><?php _e('Html Load Time','multicache-plugin');?></a></th>
						<th width="8%" class="nowrap center">
<a href="#"  class="hasTooltip sortable" tag="reports" title="Click to sort by this column"><?php _e('Reports','multicache-plugin');?></a></th>
						<th width="5%" class="nowrap center">
<a href="#"  class="hasTooltip sortable" tag="pre" title="Click to sort by this column"><?php _e('Precache','multicache-plugin');?></a></th>
						<th width="5%" class="nowrap center">
<a href="#"  class="hasTooltip sortable" tag="fast" title="Click to sort by this column"><?php _e('fastLZ','multicache-plugin');?></a></th>
						<th width="5%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="speed"  title="Click to sort by this column"><?php _e('PageSpeed','multicache-plugin');?></a></th>
						<th width="5%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="yslow" title="Click to sort by this column"><?php _e('YSlow','multicache-plugin');?>YSlow</a></th>
						<th width="5%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="pagee"  title="Click to sort by this column"><?php _e('Page Elements','multicache-plugin');?></a></th>
						<th width="5%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="htmlsize" title="Click to sort by this column"><?php _e('Html Size','multicache-plugin');?>(KB)</a></th>
						<th width="5%" class="nowrap center hidden-phone">
<a href="#"  class="hasTooltip sortable" tag="pagesize" title="Click to sort by this column"><?php _e('Page Size','multicache-plugin');?>(KB)</a></th>

</tr>
</thead>
<tbody>

<?php foreach($result_obj As $i => $item):
$pageLoadTime = number_format(($item->page_load_time) / 1000, 2);
//$pageLoadTime = number_format(($item->page_load_time) / 1000, 2);
if($tolerance->tolerance_highlighting === 0):
$tr_class = '';
$tr_style = "";
elseif ($item->status == 'test_abandoned'):
$tr_class = " bg-danger alert alert-danger ";
$tr_style = "";
elseif (($pageLoadTime > $tolerance->danger_tolerance_factor * $options['targetpageloadtime']) && $item->status == 'complete'):
$tr_class = "";
$tr_style = "color: $tolerance->danger_tolerance_color";
elseif (($pageLoadTime > $tolerance->warning_tolerance_factor * $options['targetpageloadtime']) && $item->status == 'complete'):
$tr_class = "";
$tr_style = "color: $tolerance->warning_tolerance_color";
elseif ($pageLoadTime < $options['targetpageloadtime'] && $item->status == 'complete'):
$tr_class = "";
$tr_style = "color: $tolerance->success_tolerance_color";
else:
$tr_class = "";
$tr_style = "";
endif;
        ?><tr class="row<?php echo $i %2; echo  $tr_class;?>" style="<?php echo $tr_style;?>">
    
    <td class="center hidden-phone">
<input class="<?php echo  $tr_class;?>cache_box_check" type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $item->id;?>" ></td>
<td class="<?php echo  $tr_class;?>center hidden-phone has-context" style="<?php echo $tr_style;?>"><?php echo $item->id;?></td>
<td class="<?php echo  $tr_class;?>nowrap center " style="<?php echo $tr_style;?>"><?php echo $item->date_of_test;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $pageLoadTime;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo number_format(($item->html_load_time) / 1000, 2);?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php
    // echo $item->status;
    if ($item->status == 'test_abandoned'):
         _e('abandoned','multicache-plugin');
    elseif ($item->status == 'complete'):?><a href="<?php  echo $item->report_url;?>" target="_blank"><?php  echo preg_replace('/\/$/', '', (str_ireplace('http://', '', str_ireplace('https://', '', $item->test_page))));?></a>
<?php
    elseif ($item->status == 'initiated' || $item->status == 'test_started' || $item->status == 'test_recorded' || $item->status == 'cache_strategy_ready' || $item->status == 'page_pinged' || $item->status == 'cache_cleaned'):
        _e('in progress..','multicache-plugin');
    elseif ($item->status == 'test_on_hold'):
        _e('on hold..','multicache-plugin');
    elseif ($item->status == 'daily_budget_complete'):
        _e('awaiting credit top up..','multicache-plugin');
    endif;
    ?></td>
    <td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $item->precache_factor;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo number_format($item->cache_compression_factor, 2);?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $item->pagespeed_score;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $item->yslow_score;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo $item->page_elements;?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo number_format(($item->html_bytes / 1024), 2);?></td>
<td class="<?php echo  $tr_class;?>nowrap hascontext center" style="<?php echo $tr_style;?>"><?php echo number_format(($item->page_bytes / 1024), 2);?></td>
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
<input type="hidden" name="screen_name" value="msd" />
<input id="actionType" value="" type="hidden">

</form>
	</div>
	
	<?php 
}