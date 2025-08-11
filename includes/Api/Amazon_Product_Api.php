<?php
namespace Amazon\Affiliate\Api;

/**
 * This Amazon api handler
 *
 * @package Amazon\Affiliate\Api
 */

class Amazon_Product_Api {

    private $accessKey       = null;
    private $secretKey       = null;
    private $path            = null;
    private $regionName      = null;
    private $serviceName     = null;
    private $httpMethodName  = null;
    private $queryParametes  = array ();
    private $awsHeaders      = array ();
    private $payload         = "";
    private $HMACAlgorithm   = "AWS4-HMAC-SHA256";
    private $aws4Request     = "aws4_request";
    private $strSignedHeader = null;
    private $xAmzDate        = null;
    private $currentDate     = null;
    private $requestPath     = null;

    public function __construct( $accessKey, $secretKey, $region, $serviceName, $uriPath, $payload, $host, $items_type) {

        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->xAmzDate = $this->getTimeStamp ();
        $this->currentDate = $this->getDate ();

        $this->setRegionName($region);
        $this->setServiceName($serviceName);
        $this->setPath ($uriPath);
        $this->setPayload ($payload);
        $this->setRequestMethod ("POST");
        $this->addHeader ('content-encoding', 'amz-1.0');
        $this->addHeader ('content-type', 'application/json; charset=utf-8');
        $this->addHeader ('host', $host);

        if ('SearchItems' === $items_type) {

            $this->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems');

        } else if('getVariation' === $items_type){

            $this->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetVariations');
        }

        else {

            $this->addHeader( 'x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems' );
        }

        $this->requestPath = 'https://' . $host . $uriPath;
    }


    // Static method to get API usage
    public static function get_api_usage() {
        self::check_and_reset_counter();
        $used_requests = get_option('amazon_api_requests_made', 0);
        $total_requests = 8640; // or whatever your daily limit is
        $used_percentage = ($total_requests > 0) ? round(($used_requests / $total_requests) * 100, 2) : 0;
        return [
            'used_requests' => $used_requests,
            'total_requests' => $total_requests,
            'used_percentage' => $used_percentage
        ];
    }

    // Static method to check and reset the counter daily
    private static function check_and_reset_counter() {
        $last_reset = get_option('amazon_api_last_reset', '');
        $today = date('Y-m-d');
        if ($last_reset !== $today) {
            update_option('amazon_api_requests_made', 0);
            update_option('amazon_api_last_reset', $today);
        }
    }

    // Instance method to make a request
    public function do_request() {
        self::check_and_reset_counter();
        $current_requests = get_option('amazon_api_requests_made', 0);
        
        $headers = $this->getHeaders();
        $headerString = "";
        foreach ($headers as $key => $value) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }
        $args = array(
            'headers' => $headerString,
            'method' => 'POST',
            'body' => $this->payload
        );
        
        $fp = wp_remote_request( $this->requestPath, $args );
        if (!$fp) {
            error_log( "Connection WP_REMOTE Exception Occurred" );
            return null;
        }
        
        $body = wp_remote_retrieve_body( $fp );
        
        // Increment and store the updated request count
        update_option('amazon_api_requests_made', $current_requests + 1);
        
        return json_decode( $body );
    }

    // Function to be used in your dashboard or wherever you need to get the API usage
    public function get_amazon_api_usage() {
        return Amazon\Affiliate\Api\Amazon_Product_Api::get_api_usage();
    }

    function setPath( $path ) {

        $this->path = $path;

    }


    function setServiceName( $serviceName ) {

        $this->serviceName = $serviceName;
    }


    function setRegionName( $regionName ) {

        $this->regionName = $regionName;

    }


    function setPayload( $payload ) {

        $this->payload = $payload;
    }



    function setRequestMethod( $method ) {

        $this->httpMethodName = $method;
    }

    function addHeader( $headerName, $headerValue ) {

        $this->awsHeaders[ $headerName ] = $headerValue;
    }

    private function prepareCanonicalRequest() {
        $canonicalURL = "";
        $canonicalURL .= $this->httpMethodName . "\n";
        $canonicalURL .= $this->path . "\n" . "\n";
        $signedHeaders = '';

        foreach ( $this->awsHeaders as $key => $value ) {

            $signedHeaders .= $key . ";";

            $canonicalURL .= $key . ":" . $value . "\n";

        }

        $canonicalURL .= "\n";

        $this->strSignedHeader = substr ( $signedHeaders, 0, - 1 );

        $canonicalURL .= $this->strSignedHeader . "\n";

        $canonicalURL .= $this->generateHex ( $this->payload );

        return $canonicalURL;

    }

    private function prepareStringToSign( $canonicalURL ) {

        $stringToSign = '';
        $stringToSign .= $this->HMACAlgorithm . "\n";
        $stringToSign .= $this->xAmzDate . "\n";
        $stringToSign .= $this->currentDate . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "\n";
        $stringToSign .= $this->generateHex ( $canonicalURL );
        return $stringToSign;
    }


    private function calculateSignature( $stringToSign ) {

        $signatureKey = $this->getSignatureKey ( $this->secretKey, $this->currentDate, $this->regionName, $this->serviceName );

        $signature = hash_hmac ( "sha256", $stringToSign, $signatureKey, true );

        $strHexSignature = strtolower ( bin2hex ( $signature ) );

        return $strHexSignature;

    }


    public function getHeaders() {

        $this->awsHeaders['x-amz-date'] = $this->xAmzDate;

        ksort ( $this->awsHeaders );

        // Step 1: CREATE A CANONICAL REQUEST

        $canonicalURL = $this->prepareCanonicalRequest ();

        // Step 2: CREATE THE STRING TO SIGN

        $stringToSign = $this->prepareStringToSign ( $canonicalURL );

        // Step 3: CALCULATE THE SIGNATURE

        $signature = $this->calculateSignature ( $stringToSign );

        // Step 4: CALCULATE AUTHORIZATION HEADER

        if ( $signature ) {

            $this->awsHeaders ['Authorization'] = $this->buildAuthorizationString ( $signature );

            return $this->awsHeaders;
        }
    }


    private function buildAuthorizationString( $strSignature ) {

        return $this->HMACAlgorithm . " " . "Credential=" . $this->accessKey . "/" . $this->getDate () . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "," . "SignedHeaders=" . $this->strSignedHeader . "," . "Signature=" . $strSignature;

    }


    private function generateHex( $data ) {

        return strtolower(bin2hex (hash ("sha256", $data, true)));

    }


    private function getSignatureKey( $key, $date, $regionName, $serviceName ) {

        $kSecret = "AWS4" . $key;
        $kDate = hash_hmac ( "sha256", $date, $kSecret, true );
        $kRegion = hash_hmac ( "sha256", $regionName, $kDate, true );
        $kService = hash_hmac ( "sha256", $serviceName, $kRegion, true );
        $kSigning = hash_hmac ( "sha256", $this->aws4Request, $kService, true );
        return $kSigning;
    }

    private function getTimeStamp() {

        return gmdate ( "Ymd\THis\Z" );
    }

    private function getDate() {
        return gmdate ( "Ymd" );
    }

}