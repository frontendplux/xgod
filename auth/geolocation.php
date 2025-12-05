<?php
/**
 * Retrieves approximate geographic location (City, Region, Country) 
 * based on the user's IP address using a free external API.
 * * @return array|false An array of location data or false on failure.
 */
function get_ip_geolocation() {
    // 1. Get the user's public IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Handle proxies/load balancers (optional, but safer)
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // HTTP_X_FORWARDED_FOR can contain multiple IPs, take the first one
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip_address = trim($ips[0]);
    }

    // Skip local IP addresses
    if ($ip_address === '127.0.0.1' || $ip_address === '::1') {
        return ['error' => 'Cannot determine public IP address from localhost.', 'ip' => $ip_address];
    }

    // 2. Query the free ip-api.com service (Note: Check their rate limits)
    $url = "http://ip-api.com/json/{$ip_address}";

    // Use file_get_contents to fetch the JSON data
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        return ['error' => 'Error connecting to IP Geolocation service.', 'ip' => $ip_address];
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE || ($data['status'] ?? '') !== 'success') {
        return ['error' => 'IP data retrieval failed.', 'ip' => $ip_address, 'message' => $data['message'] ?? ''];
    }

    // 3. Extract and return the location data
    return [
        'ip'        => $ip_address,
        'country'   => $data['country'] ?? 'N/A',
        'countryCode' => $data['countryCode'] ?? 'N/A',
        'region'    => $data['regionName'] ?? 'N/A', // State/Province/Region
        'city'      => $data['city'] ?? 'N/A',
        'zip'       => $data['zip'] ?? 'N/A',
        'lat'       => $data['lat'] ?? null,
        'lon'       => $data['lon'] ?? null,
    ];
}