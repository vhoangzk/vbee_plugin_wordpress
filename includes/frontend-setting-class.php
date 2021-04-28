<?php
// call api
add_action('wp_ajax_Vbee_action', 'Vbee_action');
add_action('wp_ajax_nopriv_Vbee_action', 'Vbee_action');
function Vbee_action() {
    check_ajax_referer( 'ajax_security', 'security' );
    if(current_user_can('administrator')){
        if(isset($_POST['post_id']) && $_POST['post_id'] ){
            $res = array(
                'nodata' => null
            );
            $content = null;
            $content_post = get_post($_POST['post_id']);
            $content = $content_post->post_content;
            $content = preg_replace("/<img[^>]+\>/i", "", $content);
            $content = wp_strip_all_tags($content);
            $vbee_api_class = new VbeeApiClass();
            $res = $vbee_api_class->call($_POST['post_id'], $content);
            update_post_meta( $_POST['post_id'], 'check_audio', 2, '');
            echo json_encode(array(
                'status' => 'oke',
                'res' => $res, 
                'content' => $content
            ));
        }
    }
    die(); 
}

// check callback
add_action('wp_ajax_VbeeActionCheck', 'VbeeActionCheck');
add_action('wp_ajax_nopriv_VbeeActionCheck', 'VbeeActionCheck');
function VbeeActionCheck() {
    check_ajax_referer( 'ajax_security', 'security' );
    if(current_user_can('administrator')){
        if(isset($_POST['post_id']) && $_POST['post_id'] ){
            $check = VbeeAdminConvert::vbee_check_isset_audio($_POST['post_id']);
            if($check){
                echo json_encode(array(
                    'status' => 1,
                    'audio' => $check
                ));
            } else {
                echo json_encode(array(
                    'status' => 0
                ));  
            }
        }
    }
    die(); 
}

// Delete file audio
add_action('wp_ajax_VbeeActionDelete', 'VbeeActionDelete');
add_action('wp_ajax_nopriv_VbeeActionDelete', 'VbeeActionDelete');
function VbeeActionDelete() {
    check_ajax_referer( 'ajax_security', 'security' );
    if(current_user_can('administrator')){
        if(isset($_POST['post_id']) && $_POST['post_id'] ){
            $file_path = ABSPATH . 'wp-content/uploads/' . FOLDER_AUDIO .'/' . $_POST['post_id'] . '.mp3';
            wp_delete_file( $file_path );
            echo json_encode(
                array(
                    'status'=> $_POST['post_id']
                )
            );
        }
    }
    die(); 
}

// insert audio before content
function vbee_insert_after($content) {
    $vbee_api_class = new VbeeApiClass();
    $check = VbeeAdminConvert::vbee_check_isset_audio(get_the_ID());
    $ads = '';
    if($check){
        $ads = '<div class="vbee-plugin"><audio controls name="media" controlsList="nodownload"><source src="'.$check.'" type="audio/mpeg"></audio></div>';
    }
    if(is_single()){
        $content = $ads.$content;
    }
    return $content;
}
add_filter( 'the_content', 'vbee_insert_after' );

// check update content wordpress
function update_check_audio($post_id) {
    update_post_meta( $post_id, 'check_audio', 0, '');
}
add_action( 'save_post', 'update_check_audio' );

// add js header website
function vbeeJsHeader(){
    global $wp_query;
    $object_name = 'vbee_ajax_object';
    $ajaxObject = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('ajax_security'),
        'urlhome' => home_url(),
        'post_id' => is_single() ? get_the_ID() : 0,
        'confirm' => __("Xác nhận", "vbee"),
        'is_page' => is_home() ? 'home' : (is_page() ? 'page' : (is_single() ? 'single' : (is_category() ? 'category' : (is_404() ? 'page-404': (is_tag() ? 'tag': 'is_admin'))))) 
    );

    foreach ( (array) $ajaxObject as $key => $value ) {
        if ( !is_scalar($value) )
            continue;
        if ( is_numeric($value) )
            continue;
        $ajaxObject[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
    }

    $script = "var $object_name = " . wp_json_encode( $ajaxObject ) . ';';
    echo "<script type='text/javascript'>\n";
    echo "/* <![CDATA[ */\n";
    echo $script;
    echo "\n/* ]]> */\n";
    echo "</script>\n";
}
add_action ( 'wp_head', 'vbeeJsHeader', 5 );
add_action ( 'admin_head', 'vbeeJsHeader', 5 );