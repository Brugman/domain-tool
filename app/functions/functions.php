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
    return ( getenv('APP_PASSWORD') ? getenv('APP_PASSWORD') : 'password-missing-in-dotenv' );
}

function get_results( $domain_raw = false )
{
    if ( !$domain_raw )
        return false;

    $results = [];

    // domain
    $results['domain'] = strip_tags( $_GET['domain'] );

    // dns
    $dns_data = dns_get_record( $results['domain'], DNS_NS + DNS_MX + DNS_A );

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

    if ( isset( $results['ns'] ) )
        sort( $results['ns'] );

    if ( isset( $results['mx'] ) )
        sort( $results['mx'] );

    // ssl
    $results['ssl'] = ssl_status( $results['domain'] );

    // http version
    $results['http_version'] = http_version( $results['domain'] );

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

function http_version( $domain )
{
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $domain );
    // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_CAINFO, dirname( __FILE__ ).'/cacert.pem' );
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_VERSION_HTTP2 );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
    $response = curl_exec( $ch );

    preg_match_all( '#HTTP/([[:digit:]][^\s]*)#', $response, $matches );
    $group = end( $matches );
    $http_version = end( $group );

    if ( isset( $http_version ) && !empty( $http_version ) )
        return $http_version;

    return false;
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
        echo '<p>Nameserver(s) could not be found.</p>';

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
        echo '<p>Mailserver(s) could not be found.</p>';

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
        echo '<p>Webserver(s) could not be found.</p>';

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

    if ( $ssl == 0 )
        $output = 'No.';
    if ( $ssl == 1 )
        $output = 'Yes.';

    echo '<p>'.$output.'</p>';
}

function display_results_http( $http = false )
{
    $output = 'Could not be determined.';

    if ( $http && !empty( $http ) )
        $output = $http;

    echo '<p>'.$output.'</p>';
}
