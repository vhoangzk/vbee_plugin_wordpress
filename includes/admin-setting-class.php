<?php

class VbeeSettingsPage {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct(){
        add_action( 'admin_menu', array( $this, 'vbee_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'vbee_page_init' ) );
    }

    /**
     * Add options page
     */
    public function vbee_plugin_page(){
        // This page will be under "Settings"
        add_menu_page(
            'Vbee', 
            'Vbee Setting', 
            'manage_options', 
            'vbee-setting-admin',
            array( $this, 'vbee_create_admin_page' ),
            'dashicons-buddicons-replies'
        );
    }

    /**
     * Options page callback
     */
    public function vbee_create_admin_page(){
        // Set class property
        $this->options = get_option( 'vbee-options' );
        ?>
        <div class="wrap">
            <h1>Vbee Setting</h1>
            <form method="post" action="options.php" class="option-style">
            <?php
                settings_fields( 'vbee_option_group' );
                do_settings_sections( 'vbee-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function vbee_page_init(){        
        register_setting(
            'vbee_option_group', // Option group
            'vbee-options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_1', // ID
            'General', // Title
            array( $this, 'print_section_info' ), // Callback
            'vbee-setting-admin' // Page
        );

        add_settings_field(
            'address', 
            'API Address Url', 
            array( $this, 'url_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_field(
            'appid', 
            'App ID', 
            array( $this, 'appid_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_field(
            'audiotype', 
            'Audio Type', 
            array( $this, 'audiotype_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_field(
            'bitrate', 
            'Bit Rate', 
            array( $this, 'bitrate_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_field(
            'timebreakaftertitle', 
            'Time break after title', 
            array( $this, 'timebreakaftertitle_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_field(
            'timebreakaftersapo', 
            'Time break after sapo', 
            array( $this, 'timebreakaftersapo_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_field(
            'timebreakofparagraph', 
            'Time break of paragraph', 
            array( $this, 'timebreakofparagraph_callback' ), 
            'vbee-setting-admin', 
            'setting_section_1'
        );

        add_settings_section(
            'setting_section_2', // ID
            'Voices', // Title
            array( $this, 'print_section_info' ), // Callback
            'vbee-setting-admin' // Page
        );

        add_settings_field(
            'id', 
            'ID', 
            array( $this, 'id_callback' ), 
            'vbee-setting-admin', 
            'setting_section_2'
        );

        add_settings_field(
            'rate', 
            'Rate', 
            array( $this, 'rate_callback' ), 
            'vbee-setting-admin', 
            'setting_section_2'
        );
        add_settings_section(
            'setting_section_3', // ID
            'Other settings ', // Title
            array( $this, 'print_section_info' ), // Callback
            'vbee-setting-admin' // Page
        );
        add_settings_field(
            'clear', 
            'Clear vbee database when deactivating plugin', 
            array( $this, 'clear_callback' ), 
            'vbee-setting-admin', 
            'setting_section_3'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ){
        $new_input = array();
        if( isset( $input['address'] ) ) $new_input['address'] = ( $input['address'] );
        if( isset( $input['appid'] ) ) $new_input['appid'] = sanitize_text_field( $input['appid'] );
        if( isset( $input['audiotype'] ) ) $new_input['audiotype'] = sanitize_text_field( $input['audiotype'] );
        if( isset( $input['bitrate'] ) ) $new_input['bitrate'] = sanitize_text_field( $input['bitrate'] );
        if( isset( $input['timebreakaftertitle'] ) ) $new_input['timebreakaftertitle'] = sanitize_text_field( $input['timebreakaftertitle'] );
        if( isset( $input['timebreakaftersapo'] ) ) $new_input['timebreakaftersapo'] = sanitize_text_field( $input['timebreakaftersapo'] );
        if( isset( $input['timebreakofparagraph'] ) ) $new_input['timebreakofparagraph'] = sanitize_text_field( $input['timebreakofparagraph'] );
        if( isset( $input['id'] ) ) $new_input['id'] = sanitize_text_field( $input['id'] );
        if( isset( $input['rate'] ) ) $new_input['rate'] = sanitize_text_field( $input['rate'] );
        if( isset( $input['clear'] ) ) $new_input['clear'] = sanitize_text_field( $input['clear'] );

        return $new_input;
    }

    /* 
     * Print the Section text
     */
    public function print_section_info(){ ?>
        <style>
    		.option-style .input-style{width: 100%;}
            .option-style .input-style{max-width: 600px;}
            .textarea-style{min-height: 100px;}
        </style>
    <?php }

    /* 
     * Callback Function 
     */

    public function url_callback(){
        printf(
            '<input class="input-style" type="text" id="address" name="vbee-options[address]" value="%s" />',
            isset( $this->options['address'] ) ? esc_attr( $this->options['address']) : 'https://vbee.vn/api/v1/convert-articles-api'
        );
    }

    public function appid_callback(){
        printf(
            '<input class="input-style" type="text" id="appid" name="vbee-options[appid]" value="%s" />',
            isset( $this->options['appid'] ) ? esc_attr( $this->options['appid']) : 'c1c4a3f82f182ba5099edb1c'
        );
    }

    public function audiotype_callback(){
        printf(
            '<input class="input-style" type="text" id="audiotype" name="vbee-options[audiotype]" value="%s" />',
            isset( $this->options['audiotype'] ) ? esc_attr( $this->options['audiotype']) : 'mp3'
        );
    }

    public function bitrate_callback(){
        printf(
            '<input class="input-style" type="number" id="bitrate" name="vbee-options[bitrate]" value="%s" />',
            isset( $this->options['bitrate'] ) ? esc_attr( $this->options['bitrate']) : 128000
        );
    }

    public function timebreakaftertitle_callback(){
        printf(
            '<input class="input-style" type="number" id="timebreakaftertitle" name="vbee-options[timebreakaftertitle]" value="%s" />',
            isset( $this->options['timebreakaftertitle'] ) ? esc_attr( $this->options['timebreakaftertitle']) : 0.5
        );
    }

    public function timebreakaftersapo_callback(){
        printf(
            '<input class="input-style" type="number" id="timebreakaftersapo" name="vbee-options[timebreakaftersapo]" value="%s" />',
            isset( $this->options['timebreakaftersapo'] ) ? esc_attr( $this->options['timebreakaftersapo']) : 0.5
        );
    }

    public function timebreakofparagraph_callback(){
        printf(
            '<input class="input-style" type="number" id="timebreakofparagraph" name="vbee-options[timebreakofparagraph]" value="%s" />',
            isset( $this->options['timebreakofparagraph'] ) ? esc_attr( $this->options['timebreakofparagraph']) : 0.5
        );
    }

    public function id_callback(){
        printf(
            '<input class="input-style" type="text" id="id" name="vbee-options[id]" value="%s" />',
            isset( $this->options['id'] ) ? esc_attr( $this->options['id']) : 'sg_male_minhhoang_news_48k-hsmm'
        );
    }

    public function rate_callback(){
        printf(
            '<input class="input-style" type="number" id="rate" name="vbee-options[rate]" value="%s" />',
            isset( $this->options['rate'] ) ? esc_attr( $this->options['rate']) : 1
        );
    }
    public function clear_callback(){ ?>
        <select class="input-style" name="vbee-options[clear]" id="clear">
            <option value="no" <?php if(isset($this->options['clear']) &&  $this->options['clear'] == 'no') { echo 'selected'; } ?>>No</option>
            <option value="yes" <?php if(isset($this->options['clear']) &&  $this->options['clear'] == 'yes') { echo 'selected'; } ?>>Yes</option>
        </select>
    <?php }

}
if( is_admin() ) $vbee_settings_page = new VbeeSettingsPage();