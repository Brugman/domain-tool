<?php

function d( $var = false )
{
    echo "<pre style=\"max-height:35vh;z-index:9999;position:relative;overflow-y:scroll;white-space:pre-wrap;word-wrap:break-word;padding:10px 15px;border:1px solid #fff;background-color:#161616;text-align:left;line-height:1.5;font-family:Courier;font-size:16px;color:#fff;\">";
    print_r( $var );
    echo "</pre>";
}

function dump( $var = false )
{
    d( $var );
}

function dd( $var = false )
{
    d( $var );
    exit;
}

function access_password()
{
    return getenv('APP_PASSWORD');
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
    $results['ns'] = false;
    $results['a'] = false;
    $results['mx'] = false;

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

    if ( is_array( $results['ns'] ) )
        sort( $results['ns'] );

    if ( is_array( $results['mx'] ) )
        sort( $results['mx'] );

    // ssl
    $results['ssl'] = ssl_status( $results['domain'] );

    $response = get_response( $results['domain'] );
    $response = remove_redirects( $response );

    // http version
    $results['http_version'] = http_version( $response );

    // server software
    $results['server_software'] = server_software( $response );

    // php version
    $results['php_version'] = php_version( $response );

    return $results;
}

function whois_info( $domain = false )
{
    if ( !$domain )
        return false;

    $parts = explode( '.', $domain );
    $tld   = end( $parts );

    if ( $tld == 'nl' )
    {
        $info = [
            'tld'      => $tld,
            'provider' => 'SIDN',
            'link'     => 'https://www.sidn.nl/whois/?q='.$domain,
        ];
    }
    else
    {
        $info = [
            'tld'      => $tld,
            'provider' => 'TransIP',
            'link'     => 'https://www.transip.nl/whois/prm/'.$domain,
        ];
    }

    return $info;
}

function whois_link( $domain )
{
    $parts = explode( '.', $domain );

    if ( end( $parts ) == 'nl' )
        return 'https://www.sidn.nl/whois/?q='.$domain;

    return 'https://www.transip.nl/whois/prm/'.$domain;
}

function ssl_status( $domain )
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

function get_response( $domain )
{
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $domain );
    curl_setopt( $ch, CURLOPT_CAINFO, dirname( __FILE__ ).'/cacert.pem' );
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_VERSION_HTTP2 );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
    curl_setopt( $ch, CURLOPT_NOBODY, true );

    $headers = curl_exec( $ch );

    return $headers;
}

function remove_redirects( $response )
{
    $response = trim( $response );

    $servers = explode( PHP_EOL.PHP_EOL, $response );

    if ( count( $servers ) > 1 )
        return end( $servers );

    return $response;
}

function php_version( $response )
{
    preg_match_all( '#x-powered-by: PHP\/(.+)#i', $response, $matches );

    $php_version = $matches[1][0] ?? false;

    return $php_version;
}

function server_software( $response )
{
    preg_match_all( '#Server: (.+)#i', $response, $matches );

    $server_software = $matches[1][0] ?? false;

    if ( !$server_software )
        return false;

    $server_software = str_replace( '/', ' ', $server_software );

    $server_software = strtr( $server_software, [
        'cloudflare-nginx' => 'Cloudflare NGINX',
        'nginx'            => 'NGINX',
        'lighttpd'         => 'Lighttpd',
    ]);

    return $server_software;
}

function http_version( $response )
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

function include_svg( $filename = false )
{
    if ( !$filename )
        return;

    return file_get_contents( dirname( dirname( __DIR__ ) ).'/public_html/assets/images/'.$filename.'.svg' );
}

function display_results_whois( $domain = false )
{
    $whois_info = whois_info( $domain );

    echo '<p><a href="'.$whois_info['link'].'" target="_blank">WHOIS this .'.$whois_info['tld'].' domain at '.$whois_info['provider'].'</a>.</p>';
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
                    $ip_html = '<span class="translated"><span class="arrow">'.include_svg( 'level-up-alt-solid' ).'</span>'.$ip.'</span>';
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
                $hostname_html = '<span class="translated"><span class="arrow">'.include_svg( 'level-up-alt-solid' ).'</span>'.$hostname.'</span>';

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

function restrict_access()
{
    if ( getenv('APP_ENV') == 'local' )
        return;

    if ( getenv('APP_PASSWORD') == '' )
        return;

    if ( isset( $_GET['password'] ) && $_GET['password'] == getenv('APP_PASSWORD') )
        return;

    exit( 'Access restricted.' );
}
