<?php

namespace MySchedule\Style;

function enqueue_style() {
	if ( is_user_logged_in() ) {
		wp_register_style( 'my_schedule_style', plugins_url( '../css/my-schedule.css', __FILE__ ) );
		wp_enqueue_style( 'my_schedule_style' );
	}
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_style' );
