<?php

/*
Plugin Name: Cleancoded Comments
Description: Analyze comments to determine which are most articulate & relevant. Place them near the top of the post.
Author: Cleancoded
Version: 1.0
Author URI: https://cleancoded.com
*/

define( 'cleancoded_COMMENTS_DIR_NAME', plugin_basename(__FILE__) );

function cleancoded_comments_text_domain() {
    load_plugin_textdomain( 'cleancoded-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

// Setup class autoloader
require_once dirname(__FILE__) . '/src/CommentIQ/Autoloader.php';
CommentIQ_Autoloader::register();

// Load Comment IQ
$commentiq_plugin = new CommentIQ_Plugin(__FILE__);
add_action( 'plugins_loaded', array( $commentiq_plugin, 'load' ) );
add_action( 'plugins_loaded', 'cleancoded_comments_text_domain' );
