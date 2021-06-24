<?php
class VbeeApiClass {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $dataApi;

    /**
     * Start up
     */
    public function __construct(){
        $this->callback();
        $this->options = get_option('vbee-options');
        $voices = [];
        if (isset($this->options['id1'])) $voices[] = [
            "id" => $this->options['id1'] ? $this->options['id1'] : "hn_female_ngochuyen_news_48k-thg",
            "rate" => $this->options['rate'] ? $this->options['rate'] : 1
        ];

        if (isset($this->options['id2'])) $voices[] = [
            "id" => $this->options['id2'] ? $this->options['id2'] : "hue_female_huonggiang_news_48k-thg",
            "rate" => $this->options['rate'] ? $this->options['rate'] : 1
        ];

        if (isset($this->options['id3'])) $voices[] = [
            "id" => $this->options['id3'] ? $this->options['id3'] : "sg_male_minhhoang_news_48k-thg",
            "rate" => $this->options['rate'] ? $this->options['rate'] : 1
        ];

        if (!isset($this->options['id1']) && !isset($this->options['id2']) && !isset($this->options['id3'])) {
            $voices[] = [
                "id" => "hn_female_ngochuyen_news_48k-thg",
                "rate" => 1
            ];
        }

        $this->dataApi = array(
            "content" => "",
            "appId" => $this->options['appid'] ? $this->options['appid'] : 'c1c4a3f82f182ba5099edb1c',
            "httpCallback" => "",
            "sampleRate" => "48000",
            "bitRate" => $this->options['bitrate'] ? $this->options['bitrate'] : "128000",
            "audioType" => $this->options['audiotype'] ? $this->options['audiotype'] : "mp3",
            "timeBreakAfterTitle" => $this->options['timebreakaftertitle'] ? $this->options['timebreakaftertitle'] : "0.5",
            "timeBreakAfterSapo" => $this->options['timebreakaftersapo'] ? $this->options['timebreakaftersapo'] : "0.5",
            "timeBreakOfParagraph" => $this->options['timebreakofparagraph'] ? $this->options['timebreakofparagraph'] : "0.5",
            "voices" => $voices
        );
    }

    // call api
    public function call($id, $content){
        $this->dataApi['content'] = $content;
        $this->dataApi['httpCallback'] = get_the_permalink($id);
        $urlApi = $this->options['address'];

        $args = array(
            'body'        => json_encode($this->dataApi),
            'timeout'     => '0',
            'redirection' => '5',
            'httpversion' => '2',
            'blocking'    => true,
            'headers'     => array(
                "Content-Type" => "application/json"
            ),
            'cookies'     => array(),
        );


        $response = wp_remote_post($urlApi, $args);

        return array(
            'res' => $response,
            'linkCallback' => $this->dataApi['httpCallback'] 
        );
    }

    // callback and save database
    public function callback(){
        if(is_single()){
            $post_id = get_the_ID();
            $postdata = file_get_contents('php://input');
            $postdatajson = json_decode($postdata, true);
            if(isset($postdatajson['status'])){
                $postdatajson['status'] = (int)$postdatajson['status'];
                if($postdatajson['status'] != 0) {
                    update_post_meta( $post_id, 'check_audio', 3, '');
                    return false;
                    exit();
                } else {
                    if(isset($postdatajson['url'])){
                        if ( ! function_exists( 'download_url' ) ) {
                            require_once ABSPATH . 'wp-admin/includes/file.php';
                        }
                        $link  = $postdatajson['url'];
                        $voice  = $postdatajson['voice'];
                        $tmp_file = download_url( $link );
                        $upload_dir = wp_upload_dir();
                        $dir_path = $upload_dir['basedir'] . '/' . VBEE_FOLDER_AUDIO;
                        if (!file_exists($dir_path)) {
                            wp_mkdir_p($dir_path);
                        }
                        $filepath = $dir_path .'/' . $post_id . '--' . $voice . '.mp3';
                        copy( $tmp_file, $filepath );
                        @unlink( $tmp_file );

                        $audio = get_post_meta( $post_id, 'audio', false);
                        update_post_meta( $post_id, 'check_audio', 1, '');
                        if($audio != ''){
                            update_post_meta( $post_id, 'audio', $link, '' );
                        } else {
                            add_post_meta( $post_id, 'audio', $link );
                        }
                    }
                }
            } else {
                return false;
                exit();
            }
        }
    }
}