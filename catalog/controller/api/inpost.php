<?php
/**
 *  Avatec Inpost Integration
 *  Copyright (c) 2020 Grzegorz Miskiewicz
 *  All Rights Reserved
 */


class ControllerApiInpost extends Controller {

    public function index()
    {
        $this->load->model('extension/shipping/inpost');

        $data = $this->model_extension_shipping_inpost->getJson();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'data' => $data
        ]));
    }
}
