<?php
class QSS_Client {
    const API_BASE_URL = 'https://symfony-skeleton.q-tests.com/api';

    private $email;
    private $password;
    private $token;

    public function __construct( $email, $password ) {
        $this->email = $email;
        $this->password = $password;
    }

    private function request_token() {
    $url = self::API_BASE_URL . '/login_check';

    $response = wp_remote_post( $url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode( array(
            '_username' => $this->email,
            '_password' => $this->password,
        ) ),
    ) );

    if ( is_wp_error( $response ) ) {
        throw new Exception( 'Failed to retrieve token: ' . $response->get_error_message() );
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( empty( $data->token ) ) {
        throw new Exception( 'Failed to retrieve token: invalid response from server.' );
    }

    $this->token = $data->token;

    // Set the token as a cookie
    setcookie( 'qss_token', $this->token, time() + 3600, '/' );
    }
    
    public function get( $path, $args = array() ) {
        if ( ! $this->token ) {
            $this->request_token();
        }

        $url = self::API_BASE_URL . $path;

        $args = wp_parse_args( $args, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->token,
            ),
        ) );

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            throw new Exception( 'Failed to retrieve data: ' . $response->get_error_message() );
        }

        return json_decode( wp_remote_retrieve_body( $response ) );
    }
}
