<?php

namespace MySchedule\Settings;

function get_options_time_frame(): array {
	return array(
		'1 week'   => __( '1 week', 'my-schedule' ),
		'1 month'  => __( '1 month', 'my-schedule' ),
		'3 months' => __( '3 months', 'my-schedule' )
	);
}

function get_option_value_time_frame( $key ): string {
	$key = ( $key == 'default' || $key == '' || ! array_key_exists( $key, get_options_time_frame() ) ) ? 'week' : $key;
	return $key;
}

function get_options_publishing_days(): array {
	return array(
		'publish_on_monday'    => __( 'Monday', 'my-schedule' ),
		'publish_on_tuesday'   => __( 'Tuesday', 'my-schedule' ),
		'publish_on_wednesday' => __( 'Wednesday', 'my-schedule' ),
		'publish_on_thursday'  => __( 'Thursday', 'my-schedule' ),
		'publish_on_friday'    => __( 'Friday', 'my-schedule' ),
		'publish_on_saturday'  => __( 'Saturday', 'my-schedule' ),
		'publish_on_sunday'    => __( 'Sunday', 'my-schedule' )
	);
}

function create_settings_page() {
	add_options_page( __( 'My schedule', 'my-schedule' ), __( 'My schedule', 'my-schedule' ), 'manage_options', 'my-schedule', __NAMESPACE__ . '\settings_page' );
}

add_action( 'admin_menu', __NAMESPACE__ . '\create_settings_page' );

function settings_page() {

	echo "<h2>" . __( 'My schedule settings', 'my-schedule' ) . "</h2>";
	echo "<form action='options.php' method='post'>";
	settings_fields( 'my_schedule_plugin_options' );
	do_settings_sections( 'my-schedule' );
	submit_button();
	echo "</form>";

}

function section_time_frame_text() {
	echo '<p>' . __( 'Here you can set all the options for the time frame', 'my-schedule' ) . '</p>';
}

function section_schedule_text() {
	echo '<p>' . __( 'Here you can set all the options for the schedule' ) . '</p>';
}

function setting_time_frame() {
	$available_options = get_options_time_frame();
	$options           = get_option( 'my_schedule_plugin_options' );
	$option            = ( isset( $options['time_frame'] ) && $options['time_frame'] != "" ) ? $options['time_frame'] : "week";

	echo '<select name="my_schedule_plugin_options[time_frame]" id="my_schedule_setting_time_frame">';

	foreach ( $available_options as $key => $value ) {
		$selected = ( $option == $key ) ? "selected" : "";
		echo "<option value=\"$key\" $selected>$value</option>";
	}
	echo '</select>';
}

function setting_schedule() {
	$available_options = get_options_publishing_days();
	$options           = get_option( 'my_schedule_plugin_options' );
	$option            = array();
	foreach ( $available_options as $key => $value ) {
		$option[ $key ] = ( isset( $options[ $key ] ) && $options[ $key ] != "" ) ? $options[ $key ] : false;
	}
	foreach ( $available_options as $key => $value ) {
		$selected = ( $option[ $key ] == true ) ? "checked" : "";
		echo "<input $selected name=\"my_schedule_plugin_options[$key]\" id=\"$key\" type=\"checkbox\"/><label for=\"$key\">$value</label><br>";
	}
}

function register_settings() {
	register_setting( 'my_schedule_plugin_options', 'my_schedule_plugin_options', __NAMESPACE__ . '\plugin_options_validate' );
	add_settings_section( 'time_frame', __( 'Time frame', 'my-schedule' ), __NAMESPACE__ . '\section_time_frame_text', 'my-schedule' );
	add_settings_field( 'my_schedule_setting_time_frame', __( 'Minimal time frame', 'my-schedule' ), __NAMESPACE__ . '\setting_time_frame', 'my-schedule', 'time_frame' );
	add_settings_section( 'schedule', __( 'Schedule', 'my-schedule' ), __NAMESPACE__ . '\section_schedule_text', 'my-schedule' );
	add_settings_field( 'my_schedule_setting_schedule', __( 'Publishing days', 'my-schedule' ), __NAMESPACE__ . '\setting_schedule', 'my-schedule', 'schedule' );
}

add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );

function plugin_options_validate( $input ) {
	$validated_input = array();

	if ( array_key_exists( $input['time_frame'], get_options_time_frame() ) ) {
		$validated_input['time_frame'] = $input['time_frame'];
	}

	foreach ( get_options_publishing_days() as $key => $value ) {
		if ( isset( $input[ $key ] ) && $input[ $key ] == "on" ) {
			$validated_input[ $key ] = "on";
		}
	}

	return $validated_input;
}
