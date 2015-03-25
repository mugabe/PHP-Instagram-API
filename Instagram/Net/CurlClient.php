<?php

/**
* Instagram PHP
* @author Galen Grover <galenjr@gmail.com>
* @license http://opensource.org/licenses/mit-license.php The MIT License
*/

namespace Instagram\Net;

/**
 * Curl Client
 *
 * Uses curl to access the API
 */
class CurlClient implements ClientInterface {

    /**
     * Curl Resource
     *
     * @var curl resource
     */
    protected $curl = null;

    /**
     * Constructor
     *
     * Initializes the curl object
     */
    function __construct(){
        $this->initializeCurl();
    }

    /**
     * GET
     *
     * @param string $url URL to send get request to
     * @param array $data GET data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function get( $url, array $data = null, $secret = null ){
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt( $this->curl, CURLOPT_URL, sprintf( "%s?%s", $url, http_build_query( $data ) ) );
        return $this->fetch();
    }

    /**
     * POST
     *
     * @param string $url URL to send post request to
     * @param array $data POST data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function post( $url, array $data = null, $secret = null ) {
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'POST' );
        curl_setopt( $this->curl, CURLOPT_URL, $url );

        if ($secret) {
            $ip = @$_SERVER['REMOTE_ADDR'];
            if (!$ip) $ip = '127.0.0.1';
            $signature = hash_hmac('sha256', $ip, $secret, false);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('X-Insta-Forwarded-For: ' . $ip . '|' . $signature));
        }

        curl_setopt( $this->curl, CURLOPT_POSTFIELDS, http_build_query($data));

        return $this->fetch();
    }

    /**
     * PUT
     *
     * @param string $url URL to send put request to
     * @param array $data PUT data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function put( $url, array $data = null, $secret = null  ){
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
    }

    /**
     * DELETE
     *
     * @param string $url URL to send delete request to
     * @param array $data DELETE data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function delete( $url, array $data = null, $secret = null  ){
        curl_setopt( $this->curl, CURLOPT_URL, sprintf( "%s?%s", $url, http_build_query( $data ) ) );
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
        return $this->fetch();
    }

    /**
     * Initialize curl
     *
     * Sets initial parameters on the curl object
     *
     * @access protected
     */
    protected function initializeCurl() {
        $this->curl = curl_init();
        curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, false );
    }

    /**
     * Fetch
     *
     * Execute the curl object
     *
     * @return StdClass
     * @access protected
     * @throws \Instagram\Core\ApiException
     */
    protected function fetch() {
        //curl_setopt($this->curl, CURLOPT_HEADER, 1);
        $raw_response = curl_exec( $this->curl );
        /*
        list($header, $body) = explode("\r\n\r\n", $raw_response, 2);
        $raw_response = $body;
        $headers = explode("\r\n", $header);
        $res_headers = array();
        foreach ($headers as $header) {
            if (substr($header, 0, 4) == 'HTTP') continue;
            @list($header_key, $header_value) = explode(': ', $header, 2);
            $res_headers[$header_key] = $header_value;
        }
        //print_r($headers);
        echo "  -- ".@$res_headers['X-Ratelimit-Remaining']."\n";
        */
        $error = curl_error( $this->curl );
        if ( $error ) {
            throw new \Instagram\Core\ApiException( $error, 666, 'CurlError' );
        }
        return $raw_response;
    }
}
