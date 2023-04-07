<?php
/**
 * Plugin Name: QSS Client
 * Plugin URI: 
 * Description: A plugin to connect to the Q Symfony Skeleton API.
 * Version: 1.0.0
 * Author: Melita Poropat
 * License: GPL2
 */

// Define the API endpoint and credentials
define( 'QSS_API_ENDPOINT', 'https://symfony-skeleton.q-tests.com' );
define( 'QSS_API_EMAIL', 'ahsoka.tano@q.agency' );
define( 'QSS_API_PASSWORD', 'Kryze4President' );

// Perform the login and retrieve the access token
function qss_login() {
    $url = QSS_API_ENDPOINT . '/login_check';
    $data = array(
        '_username' => QSS_API_EMAIL,
        '_password' => QSS_API_PASSWORD,
    );
    $response = wp_remote_post( $url, array(
        'body' => $data,
    ) );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );
    return $data->token;
}

// Store the access token in a cookie with a 1-hour expiration time
function qss_store_token( $token ) {
    setcookie( 'qss_token', $token, time() + 3600, '/' );
}

// Retrieve the access token from the cookie
function qss_get_token() {
    return isset( $_COOKIE['qss_token'] ) ? $_COOKIE['qss_token'] : false;
}

// Perform a GET request to the API with the access token
function qss_get( $path ) {
    $url = QSS_API_ENDPOINT . $path;
    $headers = array(
        'Authorization' => 'Bearer ' . qss_get_token(),
    );
    $response = wp_remote_get( $url, array(
        'headers' => $headers,
    ) );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body );
}

// Perform a POST request to the API with the access token
function qss_post( $path, $data ) {
    $url = QSS_API_ENDPOINT . $path;
    $headers = array(
        'Authorization' => 'Bearer ' . qss_get_token(),
        'Content-Type' => 'application/json',
    );
    $response = wp_remote_post( $url, array(
        'headers' => $headers,
        'body' => json_encode( $data ),
    ) );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body );
}

// Handle the login form submission
function qss_handle_login_form() {
    if ( isset( $_POST['qss_login_nonce'] ) && wp_verify_nonce( $_POST['qss_login_nonce'], 'qss_login' ) ) {
        $token = qss_login();
        if ( $token ) {
            qss_store_token( $token );
            wp_redirect( home_url() );
            exit;
        }
    }
}

