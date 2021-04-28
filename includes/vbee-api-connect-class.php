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
            "voices" => [
                [
                    "id" => $this->options['id'] ? $this->options['id'] : "sg_male_minhhoang_news_48k-hsmm",
                    "rate" => $this->options['rate'] ? $this->options['rate'] : 1
                ]
            ]
        );
    }

    // call api
    public function call($id, $content){
        $this->dataApi['content'] = $content;
        $this->dataApi['httpCallback'] = get_the_permalink($id);
        $urlApi = $this->options['address'];
       
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlApi,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($this->dataApi),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

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
                        $tmp_file = download_url( $link );
                        $filepath = ABSPATH . 'wp-content/uploads/' . FOLDER_AUDIO .'/' . $post_id . '.mp3';
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