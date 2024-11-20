<?php
// Mock WordPress classes that we need for testing
class WP_REST_Response {
    private $data;
    private $status;

    public function __construct($data, $status) {
        $this->data = $data;
        $this->status = $status;
    }

    public function get_data() {
        return $this->data;
    }

    public function get_status() {
        return $this->status;
    }
} 