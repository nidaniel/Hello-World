<?php
/*
Plugin Name: Simple Optimization
Plugin URI: http://net.tutsplus.com
Description: A super-simple plugin to improve your blog
Version: 1.0
Author: Jonathan Wolfe
Author URI: http://fire-studios.com
License: GPL2
.
This plugin written for NETTUTS at http://net.tutsplus.com
.
*/

// Clean up wp_head
// Remove Really simple discovery link
remove_action('wp_head', 'rsd_link');
// Remove Windows Live Writer link
remove_action('wp_head', 'wlwmanifest_link');
// Remove the version number
remove_action('wp_head', 'wp_generator');

// Remove curly quotes
remove_filter('the_content', 'wptexturize');
remove_filter('comment_text', 'wptexturize');

// Allow HTML in user profiles
remove_filter('pre_user_description', 'wp_filter_kses');

// SEO
// add tags as keywords
function tags_to_keywords(){
    global $post; // Get access to the $post object
    if(is_single() || is_page()){ // only run on posts or pages
        $tags = wp_get_post_tags($post->ID); // get post tags
        foreach($tags as $tag){ // loop through each tag
            $tag_array[] = $tag->name; // create new array with only tag names
        }
        $tag_string = implode(', ',$tag_array); // convert array into comma seperated string
        if($tag_string !== ''){ // it we have tags
            echo "<meta name='keywords' content='".$tag_string."' />\r\n"; // add meta tag to <head>
        }
    }
}
add_action('wp_head','tags_to_keywords'); // Add tags_to_keywords to wp_head function
// add except as description
function excerpt_to_description(){
    global $post; // get access to the $post object
    if(is_single() || is_page()){ // only run on posts or pages
        $all_post_content = wp_get_single_post($post->ID); // get all content from the post/page
        $excerpt = substr($all_post_content->post_content, 0, 100).' [...]'; // get first 100 characters and append "[...]" to the end
        echo "<meta name='description' content='".$excerpt."' />\r\n"; // add meta tag to <head>
    }
    else{ // only run if not a post or page
        echo "<meta name='description' content='".get_bloginfo('description')."' />\r\n"; // add meta tag to <head>
    }
}
add_action('wp_head','excerpt_to_description'); // add excerpt_to_description to wp_head function

//Optimize Database
function optimize_database(){
    global $wpdb; // get access to $wpdb object
    $all_tables = $wpdb->get_results('SHOW TABLES',ARRAY_A); // get all table names
    foreach ($all_tables as $tables){ // loop through every table name
        $table = array_values($tables); // get table name out of array
        $wpdb->query("OPTIMIZE TABLE ".$table[0]); // run the optimize SQL command on the table
    }
}
function simple_optimization_cron_on(){
    wp_schedule_event(time(), 'daily', 'optimize_database'); // rdd optimize_database to wp cron events
}
function simple_optimization_cron_off(){
    wp_clear_scheduled_hook('optimize_database'); // remove optimize_database from wp cron events
}
register_activation_hook(__FILE__,'simple_optimization_cron_on'); // run simple_optimization_cron_on at plugin activation
register_deactivation_hook(__FILE__,'simple_optimization_cron_off'); // run simple_optimization_cron_off at plugin deactivation
?>
