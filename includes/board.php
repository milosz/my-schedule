<?php

namespace MySchedule\Board;

function create_admin_menu() {
	if ( is_user_logged_in() ) {
		add_dashboard_page( __( 'My schedule', 'my-schedule' ), __( 'My schedule', 'my-schedule' ), 'read', 'my_schedule', __NAMESPACE__ . '\board_page' );
	}
}

add_action( 'admin_menu', __NAMESPACE__ . '\create_admin_menu' );

function board_page() {
	if ( ! is_user_logged_in() ) {
		exit( 0 );
	}

	$options           = get_option( 'my_schedule_plugin_options' );
	$option_time_frame = \MySchedule\Settings\get_option_value_time_frame( isset( $options['time_frame'] ) ? $options['time_frame'] : "1 week" );

	$option_publishing_days = array();
	foreach ( \MySchedule\Settings\get_options_publishing_days() as $key => $value ) {

		if ( isset( $options[ $key ] ) && $options[ $key ] == true ) {
			$weekday                  = explode( "_", $key );
			$option_publishing_days[] = ucwords( end( $weekday ) );
		}
	}

	$date_from  = new \DateTime();
	$query_args = array(
		'posts_per_page' => 200,
		'offset'         => 0,
		'post_status'    => 'future',
		'date_query'     => array( 'after' => $date_from->format( 'Y-m-d' ) - 1 )
	);
	$posts      = get_posts( $query_args );

	$newest_post_date = max( array_map( function ( $item ) {
		return $item->post_date;
	}, $posts ) );
	$date_to          = ( new \DateTime( $newest_post_date ) )->modify( "+$option_time_frame" );

	$date_interval = new \DateInterval( 'P1D' );
	$date_range    = new \DatePeriod( $date_from, $date_interval, $date_to );

	echo '<h1>' . __( 'My schedule', 'my-schedule' ) . '</h1>';
	echo '<p>';
	printf( _n( 'There is a %s scheduled post.', 'There are %s scheduled posts.', number_format_i18n( count( $posts ) ), 'my-schedule' ), count( $posts ) );
	echo '</p>';
	echo '<hr/>';

	$previous_date = null;
	foreach ( $date_range as $date ) {
		$posts_array = array_filter( array_map( function ( $item ) use ( $date ) {
			if ( ( new \DateTime( $item->post_date ) )->format( 'Ydm' ) == $date->format( 'Ydm' ) ) {
				return $item;
			} else {
				return null;
			}
		}, $posts ) );
		$posts_count = count( $posts_array );

		if ( $previous_date == null && $posts_count == 0 ) {
			continue;
		}
		if ( in_array( $date->format( 'l' ), $option_publishing_days ) ) {
			echo '<div class="schedule-box active">';
		} else {
			$css_class = ( $posts_count > 0 ) ? "inactive" : "hidden";
			echo '<div class="schedule-box ' . $css_class . '">';
		}

		echo '<table>';

		echo '<tr><td class="date">';
		echo '<div>' .
		     '<div class="day">' . $date->format( 'd' ) . '</div>' .
		     '<div class="weekday">' . __( $date->format( 'l' ), 'my-schedule' ) . '</div>' .
		     '<div class="month">' . __( $date->format( 'F' ), 'my-schedule' ) . '</div>' .
		     '<div class="year">' . $date->format( 'Y' ) . '</div>' .
		     '</div>';

		echo '</td><td>';
		foreach ( $posts_array as $post ) {
			if ( $post->post_title ) {
				echo '<div class="post">';
				echo '<p class="bolder">' . $post->post_title . '</p>';
				echo '<p class="links">' . '<a href="' . get_permalink( $post->ID ) . '">' . __( 'View', 'my-schedule' ) . '</a> ' . __( 'or' ) . ' <a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'edit', 'my-schedule' ) . '</a> ' . __( 'this post', 'my-schedule' ) . '</p>';
				echo '</div>';
			}
		}
		echo '</td></tr>';
		echo '</table>';
		echo '</div>';
		$previous_date = $date;
	}
}
