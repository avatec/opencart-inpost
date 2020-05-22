<?php
/**
 *  Avatec Inpost Integration
 *  Copyright (c) 2020 Grzegorz Miskiewicz
 *  All Rights Reserved
 */

class ModelExtensionShippingInpost extends Model {

    public function getQuote( $address )
    {
        if( empty( $this->config->get('shipping_inpost_type') )) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping_inpost WHERE CITY = '" .     ucfirst(strtolower($address['city'])) . "' OR POST_CODE = '" . $address['postcode'] . "'");
        }

        $this->load->language('extension/shipping/inpost');

        if (!$this->config->get('shipping_free_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

        // if ($this->cart->getSubTotal() < $this->config->get('shipping_free_total')) {
		//	$status = false;
		// }

        $method_data = array();

        if ($status) {
			$quote_data = array();

            foreach( $query->rows as $item ) {
                if( !empty( $item['ID'])) {
                    $inpost_id = 'inpost_' . $item['ID'];
                    $quote_data[$inpost_id] = array(
                        'code'         => 'inpost.' . $inpost_id,
                        'title'        => '(' . $item['ID'] . ') - ' . $item['STREET'] . ' ' . $item['BUILDING_NUMBER'] . ', ' . $item['CITY'],
                        'cost'         => $this->config->get('shipping_inpost_total'),
                        'tax_class_id' => $this->config->get('shipping_inpost_tax_class_id'),
                        'text'         => $this->currency->format($this->tax->calculate($this->config->get('shipping_inpost_total'), $this->config->get('shipping_inpost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
                    );
                }
            }

            // Wyświetlanie jako lista
            if( empty( $this->config->get('shipping_inpost_type')) {
                $title = $this->language->get('text_title') . ' (' . $address['city'] . ')';
            } else {
                // Wyświetlanie mapy

                $lat = 50.00;
                $lng = 21.00;
                $zoom = 9;

                $title = '<button id="showInpostMap" type="button" data-toggle="modal" data-target="#inpostModalMap">' . $this->language->get('button_select_item') . '</button>
                <div id="inpostModalMap" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Zamknij"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">' . $this->language->get('text_select_header') . '</h4>
                            </div>
                            <div class="modal-body">
                                <div class="inpost-google-map" data-lat="' . $lat . '" data-lng="' . $lng . '" data-zoom="' . $zoom . '"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->language->get('button_close') . '</button>
                                <button type="button" class="btn btn-primary">' . $this->language->get('button_select') . '</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }

		}

        $method_data = array(
                'code'       => 'inpost',
                'title'      => $title,
                'quote'      => (!empty( $quote_data ) ? $quote_data : ''),
                'sort_order' => $this->config->get('shipping_free_sort_order'),
                'error'      => false
            );

		return $method_data;
    }
}
