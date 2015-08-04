<?php
/**
 * Colors Section
 */
$wp_customize->add_section( 'church_metrics_dashboard_colors', array(
	'title'			=> __( 'Colors', 'church-metrics-dashboard' ),
	'panel'			=> 'church_metrics_dashboard',
	'priority'		=> 120,
) );

// Primary Number Color
$wp_customize->add_setting( 'church_metrics_dashboard_primary_color', array(
	'type'				=> 'theme_mod',
	'transport'			=> 'refresh',
	'default'			=> '#7fc661',
) );

$wp_customize->add_control(
	new WP_Customize_Color_Control( $wp_customize, 'church_metrics_dashboard_primary_color',
		array(
			'label'		=> __( 'Primary Number Color', 'church-metrics-dashboard' ),
			'section'	=> 'church_metrics_dashboard_colors',
		)
	)
);

// Comparision Number Color
$wp_customize->add_setting( 'church_metrics_dashboard_comparison_color', array(
	'type'				=> 'theme_mod',
	'transport'			=> 'refresh',
	'default'			=> '#64b4f2',
) );

$wp_customize->add_control(
	new WP_Customize_Color_Control( $wp_customize, 'church_metrics_dashboard_comparison_color',
		array(
			'label'		=> __( 'Comparison Number Color', 'church-metrics-dashboard' ),
			'section'	=> 'church_metrics_dashboard_colors',
		)
	)
);

// Heading Color
$wp_customize->add_setting( 'church_metrics_dashboard_heading_color', array(
	'type'				=> 'theme_mod',
	'transport'			=> 'refresh',
	'default'			=> '#aaa',
) );

$wp_customize->add_control(
	new WP_Customize_Color_Control( $wp_customize, 'church_metrics_dashboard_heading_color',
		array(
			'label'		=> __( 'Heading Color', 'church-metrics-dashboard' ),
			'section'	=> 'church_metrics_dashboard_colors',
		)
	)
);