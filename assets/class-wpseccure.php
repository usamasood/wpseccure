<?php
namespace WPSeccurePlugin;

function writeString($testvar){
    echo 'this is written string';
    echo 'and this is another .. ' . $testvar . ' .string';
}

function generateHashfromURL($url, $hash_type = 'sha384'){

    // ALLOWED HASHES FOR SUB-RESOURCE INTEGRITY ATTRIBUTE
    $hash_functions = array('sha256', 'sha384', 'sha512');

    // ALLOWED EXTENSIONS
    $allowed_file_extensions = array('css', 'js');

    $hash_type = strtolower($hash_type);
    $file_ext = pathinfo($url, PATHINFO_EXTENSION);

    //// VALIDATIONS
    // 1. URL structure validation
    // 2. Check if file exists (not a 404)
    // 3. Check file extension (to be js or css)
    // 4. Validate Hash type

    // VALIDATE URL AND EXTENSION
    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
        echo '<br>ERROR: Invalid url';
        return;
    }

    // CHECK IF FILE EXISTS
    $file_headers = get_headers($url);
    if(strpos($file_headers[0], '404') !== false){
        echo '<br>ERROR: File not found';
        return;
    }

    // VALIDATE FILE EXTENSION
    if (! in_array($file_ext, $allowed_file_extensions)){
        echo '<br>ERROR: File extension must be JS or CSS';
        return;
    }

    // VALIDATE HASH TYPE
    if (! in_array($hash_type, $hash_functions)){
        echo '<br>ERROR: Hash type not valid';
        return;
    }

    // CALCULATE HASH
    $url_contents = file_get_contents($url);
    $sri_attribute = base64_encode(hash($hash_type, $url_contents, true));
    return 'integrity="' . $sri_attribute . '"';

}

function get_website_ip_address($url){
    
    // $existing_ip = '165.227.200.216';
    $domain = url_to_domain($url);

    // echo "NEW DOMIAN Is {$domain} <br><br><br>";

    $ip = gethostbyname($domain);
    return $ip;

}

function get_website_nameservers($url){

    // Get SiteURL from WordPress and convert to domain
    $domain = url_to_domain($url);

    // Get Name Servers
    $results = @dns_get_record($domain, DNS_NS);

    if(!$results){
        return false;
    }
    $nameservers = array();

    foreach ($results as $key => $value) {
        if(array_key_exists('target', $value)){
            $nameservers[] = $value['target'];
        }
    }
    if(empty($nameservers)){
        return false;
    }
    return $nameservers;
}

function url_to_domain($url){
    // VALIDATE URL AND EXTENSION
    if (!filter_var($url, FILTER_VALIDATE_URL)){
        echo '<br>ERROR: Invalid url';
        return;
    }

    $domain = parse_url($url)['host'];
    return  $domain;
}

function get_current_security_headers($url){
    $security_headers = [
        'strict-transport-security',
        'x-frame-options',
        'x-content-type-options',
        'content-security-policy',
        'x-permitted-cross-domain-policies',
        'referrer-policy',
        'feature-policy',
        'x-xss-protection',
        'expect-ct'
    ];
    $headers_list = get_headers($url, 1);

    // GET ARRAY KEYS IN LOWER CASE
    $headers_list = array_change_key_case($headers_list);

    //EXTRACT ONLY ARRAY OF KEYS
    $headers_list = array_keys($headers_list);

    $found_security_headers = array_intersect($security_headers, $headers_list);
    return $found_security_headers;
}

function get_hash_from_url($url){

    // GET FILE CONTENTS
    $data = file_get_contents($url);

    // GET HASH FROM URL
    $hash =  base64_encode(hash("sha384", $data, true));

    return $hash;
}

?>