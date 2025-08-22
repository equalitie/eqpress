<?php
class CurlRequest {
    private $ch;
    /**
     *  Initialize the connection.
     *
     */
    public function init( $params ) {
        $this->ch = curl_init();
        $user_agent = 'Mozilla/5.0 (eqpress; x86_64 Linux OS 22; rv:89.0) Gecko/20160101 Firefox/89.0';
        //$header = array( 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8,application/json', 'Accept-Language: en-US,en;q=0.8', 'Accept-Charset: UTF-8', 'Keep-Alive: 300');
        //$header = array( 'Accept: */*', 'Keep-Alive: 300');
        $header = array();
        if ( isset( $params['host'] ) ) {
            $header[]="Host: " . $params['host'];
        }
        if ( isset( $params['header'] ) ) {
            $header = array_merge( $header, (array)$params['header'] );
        }
        if ( isset( $params['url'] ) ) {
            @curl_setopt( $this->ch, CURLOPT_URL, $params['url'] );
        }
        if ( isset( $params['timeout'] ) ) {
            @curl_setopt( $this->ch, CURLOPT_TIMEOUT, $params['timeout'] );
        }
        if ( isset( $params['referer'] ) ) {
            @curl_setopt ($this->ch, CURLOPT_REFERER, $params['referer'] );
        }
        if ( isset( $params['cookie'] ) ) {
            @curl_setopt ( $this->ch, CURLOPT_COOKIE, $params['cookie'] );
        }
        if ( isset( $params['post_fields'] ) ) {
            @curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $params['post_fields'] );
        }
        if ($params['method'] == 'HEAD') {
            @curl_setopt( $this->ch, CURLOPT_NOBODY, 1 );
        }
        if ( $params['method'] == 'POST' ) {
            curl_setopt( $this->ch, CURLOPT_POST, true );
        }
        if ( $params['method'] == 'PUT' ) {
            curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        if ( $params['method'] == 'DELETE' ) {
            curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        if ( isset( $params['username'] ) && isset( $params['password'] ) ) {
            @curl_setopt( $this->ch, CURLOPT_USERPWD, $params['username'] . ':' . $params['password'] );
        }
        if ( isset( $params['verbose'] ) && $params['verbose'] ) {
            $verbose = 1;
        } else {
            $verbose = 0;
        }
        @curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, TRUE );
        @curl_setopt( $this->ch, CURLOPT_VERBOSE, $verbose );
        @curl_setopt( $this->ch, CURLOPT_HEADER, TRUE );
        @curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, TRUE );
        @curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $header );
        @curl_setopt( $this->ch, CURLOPT_USERAGENT, $user_agent );
        @curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        @curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        @curl_setopt( $this->ch, CURLOPT_FORBID_REUSE, TRUE );
    }
   
    /**
     * Make curl request
     *
     * @return array  'header','body','curl_error','http_code','last_url'
     */
    public function exec() {
        $response = curl_exec( $this->ch );
        $error = curl_error( $this->ch );
        $result = array( 'header' => '',
                         'body' => '',
                         'curl_error' => '',
                         'http_code' => '',
                         'last_url' => '');

        if ( $error != "" ) {
            $result['curl_error'] = $error;
            return $result;
        }
       
        $header_size = curl_getinfo( $this->ch, CURLINFO_HEADER_SIZE );
        $result['header'] = substr( $response, 0, $header_size );
        $result['body'] = substr( $response, $header_size );
        $result['http_code'] = curl_getinfo( $this->ch, CURLINFO_HTTP_CODE );
        $result['last_url'] = curl_getinfo( $this->ch, CURLINFO_EFFECTIVE_URL );
        $result['etime'] = curl_getinfo( $this->ch, CURLINFO_TOTAL_TIME );
        return $result;
    }
}
