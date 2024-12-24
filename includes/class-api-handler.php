<?php
/**
 * Class Fetch_API_handler
 * Handles requests to an external API.
 */
class Fetch_API_handler {
    private $url;

    // endopint buscar ejercios: https://formaciomiro-cercadorapi-ne-prd-ckccggh5heckbxf7.northeurope-01.azurewebsites.net/cerca

    /**
     * Constructor that initializes the API URL.
     *
     * @param string $url The endpoint URL.
     */
    public function __construct($url) {
        $this->url = $url;
    }

    /**
     * Sends headers data to the API and returns the response.
     *
     * @param array $data Data to send.
     * @return array API response or error.
     */
    public function post_data_from_api($data) {
        $response = wp_remote_post($this->url, [
            'method' => 'POST',
            'body' => json_encode($data),
            'headers' => [
                'access_token' => 'dv7H8ZvyiwNV03R7jJBPzvYHhJSmjMlzjvcqf3s2',
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}