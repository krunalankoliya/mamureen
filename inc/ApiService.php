<?php

/**
 * Senior Developer API Service Wrapper
 * Centralizes all external API communications.
 */
class ApiService {
    
    private static $baseUrl = "https://www.talabulilm.com/api2022/core/user/";

    /**
     * Fetches ITS user details from central API
     */
    public static function getUserDetails($its_id) {
        if (empty($its_id)) return null;

        $user_its = $_COOKIE['user_its'] ?? '';
        $ver = $_COOKIE['ver'] ?? '';

        if (empty($user_its) || empty($ver)) return null;

        $api_url = self::$baseUrl . "getUserDetailsByItsID/" . urlencode($its_id);
        $auth = base64_encode("$user_its:$ver");
        $headers = ["Authorization: Basic $auth"];

        $ch = curl_init();
        // Resolve for specific server if needed (as per legacy code)
        curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local/staging development

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || empty($response)) return null;

        $data = json_decode($response, true);
        return (isset($data['its_id'])) ? $data : null;
    }
}
