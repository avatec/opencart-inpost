<?php
/**
 *  Avatec Inpost Integration
 *  Copyright (c) 2020 Grzegorz Miskiewicz
 *  All Rights Reserved
 */


class ControllerExtensionShippingInpostInpost extends Controller {

    public function index()
    {
        $this->load->model('extension/shipping/inpost');
        $data = $this->model_extension_shipping_inpost->getJson([
            'postcode' => (!empty( $this->request->get['postcode'] ) ? $this->request->get['postcode'] : null),
            'id' => (!empty( $this->request->get['id'] ) ? $this->request->get['id'] : null)
        ]);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'data' => $data
        ]));
    }

    public function setData()
    {
        $this->load->model('extension/shipping/inpost');

        $data = $this->model_extension_shipping_inpost->generateQuote( $this->request->post );

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($data);
    }
}
