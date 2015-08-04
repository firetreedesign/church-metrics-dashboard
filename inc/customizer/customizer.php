<?php
add_action( 'customize_register', 'church_metrics_dashboard_customize_register' );
function church_metrics_dashboard_customize_register( $wp_customize ) {
	
	/**
	 * Church Metrics Panel
	 */
	$wp_customize->add_panel( 'church_metrics_dashboard', array(
		'title'			=> __( 'Church Metrics Dashboard', 'church-metrics-dashboard' ),
		'description'	=> __( '<p>This panel is used to manage the display settings for Church Metrics Dashboard.</p>', 'church-metrics-dashboard' ),
		'priority'		=> 160,
	) );
	
	include_once( plugin_dir_path( __FILE__ ) . 'partials/colors.php' );
	
}

// Output the styles
function church_metrics_dashboard_customize_css() {
    
    $primary_number_color = get_theme_mod( 'church_metrics_dashboard_primary_color', '#7fc661' );
    $comparison_number_color = get_theme_mod( 'church_metrics_dashboard_comparison_color', '#64b4f2' );
    $heading_color = get_theme_mod( 'church_metrics_dashboard_heading_color', '#aaa' );
	
    ?>
         <style type="text/css">
		 	.cm_dash_widgets_value { color: <?php echo $primary_number_color; ?>; }
			.cm_dash_widgets_compare_value { color: <?php echo $comparison_number_color; ?>; }
			.cm_dash_widgets_heading { color: <?php echo $heading_color; ?>; }
         </style>
    <?php
}
add_action( 'wp_footer', 'church_metrics_dashboard_customize_css' );
add_action( 'admin_footer', 'church_metrics_dashboard_customize_css' );


function church_metrics_dashboard_submenu_page() {
	add_submenu_page( 'edit.php?post_type=cm_dash_widgets', 'Customizer', 'Customizer', 'manage_options', admin_url( 'customize.php?autofocus[panel]=church_metrics_dashboard&return=edit.php?post_type=cm_dash_widgets' ) );
}
add_action( 'admin_menu', 'church_metrics_dashboard_submenu_page' );