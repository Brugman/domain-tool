<?php

function d( $var = false )
{
    echo "<pre style=\"max-height:35vh;z-index:9999;position:relative;overflow-y:scroll;white-space:pre-wrap;word-wrap:break-word;padding:10px 15px;border:1px solid #fff;background-color:#161616;text-align:left;line-height:1.5;font-family:Courier;font-size:16px;color:#fff;\">";
    print_r( $var );
    echo "</pre>";
}

function dd( $var = false )
{
    d( $var );
    exit;
}

function access_granted()
{
    if ( getenv('APP_ENV') == 'local' )
        return true;

    if ( getenv('APP_PASSWORD') == '' )
        return true;

    if ( isset( $_GET['password'] ) && $_GET['password'] == getenv('APP_PASSWORD') )
        return true;

    return false;
}

function app_name()
{
    return 'Domain Tool';
}

function app_password()
{
    return getenv('APP_PASSWORD');
}

function app_url()
{
    if ( isset( $_GET['password'] ) && !empty( $_GET['password'] ) )
        return '/?password='.$_GET['password'];

    return '/';
}

function determine_ssl_status_with_curl( $domain )
{
    $https_domain = 'https://'.$domain;

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $https_domain );
    curl_setopt( $ch, CURLOPT_CAINFO, dirname( __FILE__ ).'/cacert.pem' );
    curl_setopt( $ch, CURLINFO_CERTINFO, true );
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
    curl_exec( $ch );

    $curl_info = curl_getinfo( $ch );

    if ( $curl_info['http_code'] == '0' )
        return 0;
    if ( $curl_info['http_code'] == '200' )
        return 1;

    return 2;
}

function get_site_with_curl( $domain )
{
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $domain );
    curl_setopt( $ch, CURLOPT_CAINFO, dirname( __FILE__ ).'/cacert.pem' );
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_VERSION_HTTP2 );
    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );

    return [
        curl_error( $ch ),
        curl_getinfo( $ch ),
        curl_exec( $ch ),
    ];
}

function extract_clean_headers( $response )
{
    $parts = explode( PHP_EOL.PHP_EOL, trim( $response ) );

    foreach ( $parts as $k => $part )
        if ( substr( $part, 0, 4 ) != 'HTTP' )
            unset( $parts[ $k ] );

    return end( $parts );
}

function determine_http_version( $response )
{
    preg_match_all( '#HTTP/([[:digit:]][^\s]*)#i', $response, $matches );

    $http_version = false;
    if ( isset( $matches[0][0] ) )
    {
        $temp = end( $matches );
        $http_version = end( $temp );
    }

    return $http_version;
}

function determine_server_software( $response )
{
    preg_match_all( '#Server: (.+)#i', $response, $matches );

    $server_software = $matches[1][0] ?? false;

    if ( !$server_software )
        return false;

    $server_software = str_replace( '/', ' ', $server_software );

    $server_software = strtr( $server_software, [
        'cloudflare-nginx' => 'Cloudflare NGINX',
        'cloudflare'       => 'Cloudflare',
        'nginx'            => 'NGINX',
        'lighttpd'         => 'Lighttpd',
    ]);

    return $server_software;
}

function determine_php_version( $response )
{
    preg_match_all( '#x-powered-by: PHP\/(.+)#i', $response, $matches );

    $php_version = $matches[1][0] ?? false;

    return $php_version;
}

function cms_is_wordpress( $html = false )
{
    if ( !$html )
        return false;

    if ( strpos( $html, 'wp-content' ) !== false )
        return true;

    if ( strpos( $html, 'wp-includes' ) !== false )
        return true;

    return false;
}

function cms_is_shopify( $html = false )
{
    if ( !$html )
        return false;

    return ( strpos( $html, 'cdn.shopify.com/s/files/' ) !== false );
}

function cms_is_joomla( $html = false )
{
    if ( !$html )
        return false;

    return ( stripos( $html, 'joomla' ) !== false );
}

function cms_is_squarespace( $html = false )
{
    if ( !$html )
        return false;

    return ( strpos( $html, 'static1.squarespace.com' ) !== false );
}

function cms_is_drupal( $html = false )
{
    if ( !$html )
        return false;

    return ( stripos( $html, 'drupal' ) !== false );
}

function cms_is_wix( $html = false )
{
    if ( !$html )
        return false;

    return ( strpos( $html, 'static.parastorage.com' ) !== false );
}

function cms_is_magento( $html = false )
{
    if ( !$html )
        return false;

    return ( strpos( $html, '/static/version' ) !== false );
}

function cms_is_opencart( $html = false )
{
    if ( !$html )
        return false;

    return ( strpos( $html, 'catalog/view/theme' ) !== false );
}

function determine_cms( $domain, $html = false )
{
    if ( cms_is_wordpress( $html ) )
        return 'WordPress';

    if ( cms_is_shopify( $html ) )
        return 'Shopify';

    if ( cms_is_joomla( $html ) )
        return 'Joomla!';

    if ( cms_is_squarespace( $html ) )
        return 'Squarespace';

    if ( cms_is_drupal( $html ) )
        return 'Drupal';

    if ( cms_is_wix( $html ) )
        return 'Wix';

    if ( cms_is_magento( $html ) )
        return 'Magento';

    if ( cms_is_opencart( $html ) )
        return 'OpenCart';

    return false;
}

function get_results()
{
    if ( !isset( $_GET['domain'] ) || empty( $_GET['domain'] ) )
        return false;

    $results = [];

    // domain
    $parse_url = $_GET['domain'];
    if ( substr( $parse_url, 0, 4 ) != 'http' )
        $parse_url = 'https://'.$parse_url;

    $results['domain'] = parse_url( $parse_url, PHP_URL_HOST );

    // dns
    $dns_data = dns_get_record( $results['domain'], DNS_NS + DNS_MX + DNS_A );

    // defaults
    $results['ns'] = [];
    $results['a']  = [];
    $results['mx'] = [];

    if ( !empty( $dns_data ) )
    {
        foreach ( $dns_data as $data )
        {
            switch ( $data['type'] )
            {
                // nameservers
                case 'NS':
                    $results['ns'][] = $data['target'];
                    break;
                // webservers
                case 'A':
                    $results['a'][] = $data['ip'];
                    break;
                // mailservers
                case 'MX':
                    $results['mx'][] = $data['target'];
                    break;
            }
        }
    }

    if ( !empty( $results['ns'] ) )
        sort( $results['ns'] );

    if ( !empty( $results['mx'] ) )
        sort( $results['mx'] );

    // ssl
    $results['ssl'] = determine_ssl_status_with_curl( $results['domain'] );

    // get site
    [ $site_errors, $site_headers, $site_response ] = get_site_with_curl( $results['domain'] );

    // extract clean headers
    $site_headers_clean = extract_clean_headers( $site_response );

    // http version
    $results['http_version'] = determine_http_version( $site_headers_clean );

    // server software
    $results['server_software'] = determine_server_software( $site_headers_clean );

    // php version
    $results['php_version'] = determine_php_version( $site_headers_clean );

    // cms
    $results['cms'] = determine_cms( $results['domain'], $site_response );

    return $results;
}

function include_svg( $filename = false )
{
    if ( !$filename )
        return;

    return file_get_contents( dirname( dirname( __DIR__ ) ).'/public_html/assets/images/'.$filename.'.svg' );
}

function whois_api_wxa( $domain = false )
{
    if ( !$domain )
        return false;

    $api_key = getenv('APP_API_KEY_WXA');

    if ( !$api_key )
        return false;

    $api_url = 'https://www.whoisxmlapi.com/whoisserver/WhoisService?domainName='.$domain.'&apiKey='.$api_key.'&outputFormat=JSON';

    $results_raw = file_get_contents( $api_url );
    $results_raw = json_decode( $results_raw, true );

    if ( isset( $results_raw['ErrorMessage'] ) )
        return false;

    $registrar_site = $results_raw['WhoisRecord']['contactEmail'] ?? false;
    if ( $registrar_site )
        $registrar_site = explode( '@', $results_raw['WhoisRecord']['contactEmail'] )[1];

    return [
        'owner_name'     => $results_raw['WhoisRecord']['registrant']['name'] ?? false,
        'owner_company'  => $results_raw['WhoisRecord']['registrant']['organization'] ?? false,
        'registrar_name' => $results_raw['WhoisRecord']['registrarName'] ?? false,
        'registrar_site' => $registrar_site,
        'created'        => $results_raw['WhoisRecord']['registryData']['createdDate'] ?? false,
    ];
}

function determine_whois_info( $domain = false )
{
    if ( !$domain )
        return false;

    if ( $whois_api_wxa = whois_api_wxa( $domain ) )
        return $whois_api_wxa;

    return false;
}

function display_whois_link( $domain = false )
{
    if ( !$domain )
        return false;

    $parts = explode( '.', $domain );
    $tld   = end( $parts );

    $info = [
        'tld'      => $tld,
        'provider' => 'TransIP',
        'link'     => 'https://www.transip.nl/whois/prm/'.$domain,
    ];

    if ( $tld == 'nl' )
    {
        $info = [
            'tld'      => $tld,
            'provider' => 'SIDN',
            'link'     => 'https://www.sidn.nl/whois/?q='.$domain,
        ];
    }

    echo '<p><a href="'.$info['link'].'" target="_blank">WHOIS this .'.$info['tld'].' domain at '.$info['provider'].'</a>.</p>';
}

function display_results_whois( $domain = false )
{
    if ( $whois_info = determine_whois_info( $domain ) )
    {
        // format
        $html_owner = $whois_info['owner_name'];
        if ( $whois_info['owner_company'] )
            $html_owner .= '<br>'.$whois_info['owner_company'];

        $html_registrar = $whois_info['registrar_name'];
        if ( $whois_info['registrar_name'] && $whois_info['registrar_site'] )
            $html_registrar = '<a href="http://'.$whois_info['registrar_site'].'" target="_blank">'.$whois_info['registrar_name'].'</a>';

        $html_created = '';
        if ( !empty( $whois_info['created'] ) )
            $html_created = date( 'Y-m-d', strtotime( $whois_info['created'] ) );

        // display
        echo '<table>';
        if ( !empty( $html_owner ) )
            echo '<tr><th>Owner</th><td>'.$html_owner.'</td></tr>';
        if ( !empty( $html_registrar ) )
            echo '<tr><th>Registrar</th><td>'.$html_registrar.'</td></tr>';
        if ( !empty( $html_created ) )
            echo '<tr><th>Created</th><td>'.$html_created.'</td></tr>';
        echo '</table>';
    }
    else
    {
        display_whois_link( $domain );
    }
}

function display_results_ns( $nameservers = false )
{
    if ( !$nameservers )
        echo '<p class="unknown">Nameserver(s) could not be found.</p>';

    if ( $nameservers && is_array( $nameservers ) )
    {
        echo '<ul>';
        foreach ( $nameservers as $nameserver )
            echo '<li>'.$nameserver.'</li>';
        echo '</ul>';
    }
}

function display_results_mx( $records = false )
{
    if ( !$records )
        echo '<p class="unknown">Mailserver(s) could not be found.</p>';

    if ( $records && is_array( $records ) )
    {
        echo '<ul>';
        foreach ( $records as $record )
        {
            $get_record_ip = false;

            $keyword = explode( '.', $record );
            $keyword = $keyword[ count( $keyword )-2 ];

            if ( strpos( $record, $keyword ) !== false )
                $get_record_ip = true;

            $ip_html = '';
            if ( $get_record_ip )
            {
                $ip = gethostbyname( $record );
                if ( $ip != $record )
                    $ip_html = '<div class="translated"><span class="arrow">'.include_svg( 'level-up-alt-solid' ).'</span>'.$ip.'</div>';
            }

            echo '<li>'.$record.$ip_html.'</li>';
        }
        echo '</ul>';
    }
}

function display_results_a( $ips = false )
{
    if ( !$ips )
        echo '<p class="unknown">Webserver(s) could not be found.</p>';

    if ( $ips && is_array( $ips ) )
    {
        echo '<ul>';
        foreach ( $ips as $ip )
        {
            $hostname_html = '';
            $hostname = gethostbyaddr( $ip );
            if ( $hostname != $ip )
                $hostname_html = '<div class="translated"><span class="arrow">'.include_svg( 'level-up-alt-solid' ).'</span>'.$hostname.'</div>';

            echo '<li>'.$ip.$hostname_html.'</li>';
        }
        echo '</ul>';
    }
}

function display_results_ssl( $ssl = false )
{
    $output = 'Could not be determined.';
    $class = 'unknown';

    if ( $ssl == 0 || $ssl == 1 )
        $class = '';

    if ( $ssl == 0 )
        $output = 'No';
    if ( $ssl == 1 )
        $output = 'Yes';

    echo '<p class="'.$class.'">'.$output.'</p>';
}

function display_results_http( $http = false )
{
    $output = 'Could not be determined.';
    $class = 'unknown';

    if ( $http && !empty( $http ) )
    {
        $output = $http;
        $class = '';
    }

    echo '<p class="'.$class.'">'.$output.'</p>';
}

function display_results_php( $php = false )
{
    $output = 'Could not be determined.';
    $class = 'unknown';

    if ( $php && !empty( $php ) )
    {
        $output = $php;
        $class = '';
    }

    echo '<p class="'.$class.'">'.$output.'</p>';
}

function display_results_software( $software = false )
{
    $output = 'Could not be determined.';
    $class = 'unknown';

    if ( $software && !empty( $software ) )
    {
        $output = $software;
        $class = '';
    }

    echo '<p class="'.$class.'">'.$output.'</p>';
}

function display_results_cms( $cms = false )
{
    $output = 'Could not be determined.';
    $class = 'unknown';

    if ( $cms && !empty( $cms ) )
    {
        $output = $cms;
        $class = '';
    }

    echo '<p class="'.$class.'">'.$output.'</p>';
}

