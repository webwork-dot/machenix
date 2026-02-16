<?php

defined('BASEPATH') OR exit('No direct script access allowed');

function json_output($statusHeader, $response) {
    $ci = & get_instance();
    $ci->output->set_content_type('application/json');
    $ci->output->set_status_header($statusHeader);
    $ci->output->set_output(json_encode($response));
}

function simple_json_output($resp) {
    header('Content-Type: application/json');
    echo json_encode($resp);
}


function json_outputs($resp) {
    header('Content-Type: application/json');
    echo json_encode(array("status" => 200, "message" => "success", "count" => sizeof($resp), "data" => $resp));
}
