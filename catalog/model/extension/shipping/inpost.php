<?php
/**
 *  Avatec Inpost Integration
 *  Copyright (c) 2020 Grzegorz Miskiewicz
 *  All Rights Reserved
 */

class ModelExtensionShippingInpost extends Model {

    public function getQuote( $address )
    {
        $this->load->language('extension/shipping/inpost');

        if( empty( $this->config->get('shipping_inpost_type') )) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping_inpost WHERE CITY = '" .     ucfirst(strtolower($address['city'])) . "' OR POST_CODE = '" . $address['postcode'] . "'");
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping_inpost");
        }

        if (!$this->config->get('shipping_inpost_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

        $type = $this->config->get('shipping_inpost_type');

        $method_data = array();

        if (!empty( $status )) {
	        $quote_data = array();

            // Wyświetlanie jako lista
            if( empty( $type ) || ($type == 0) ) {

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

                $title = $this->language->get('text_title') . ' (' . $address['city'] . ')';
            }

            // Wyswietlanie jako mapa
            if( !empty( $type ) && ( $type == 1 ) ) {
                $lat = 51.919437;
                $lng = 19.145136;
                $zoom = 7;

                $cost = $this->currency->format($this->tax->calculate($this->config->get('shipping_inpost_total'), $this->config->get('shipping_inpost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']);
                $google_api_key = $this->config->get('shipping_inpost_google_api_key');

                $title = $this->language->get('text_title') . ' - ' . $cost . '<br/>' .
                '<script src="https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '"></script>
                <button id="showInpostMap" type="button" data-toggle="modal" data-target="#inpostModalMap" class="btn btn-primary">' . $this->language->get('button_select_item') . '</button>
                <div id="inpostModalMap" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Zamknij"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">' . $this->language->get('text_select_header') . '</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group row">
                                    <div class="col-xs-12 col-md-4">
                                        <input class="form-control" type="text" name="inpost-postcode" id="inpost-postcode" placeholder="Kod pocztowy" />
                                    </div>
                                    <div class="col-xs-12 col-md-4">
                                        <input class="form-control" type="text" name="inpost-id" id="inpost-id" placeholder="Numer paczkomatu" />
                                    </div>
                                    <div class="col-xs-12 col-md-4">
                                        <button type="button" id="btnInpostFindByForm" class="btn btn-primary btn-block">
                                            Wyszukaj paczkomaty
                                        </button>
                                    </div>
                                </div>
                                <div id="inpost-google-map" style="width:100%;height:500px;" data-lat="' . $lat . '" data-lng="' . $lng . '" data-zoom="' . $zoom . '"></div>
                            </div>
                            <div class="modal-footer">
                                <button id="btnCloseInpostModal" type="button" class="btn btn-default" data-dismiss="modal" data-target="#inpostModalMap">' . $this->language->get('button_close') . '</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }


            $method_data = array(
                'code'       => 'inpost',
                'title'      => $title,
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('shipping_free_sort_order'),
                'error'      => false
            );

		}

		return $method_data;
    }

    public function getJson( $data = null )
    {
        if( !empty( $data['postcode'] )) {
            $q[] = "POST_CODE='" . $data['postcode'] . "'";
        }
        if( !empty( $data['id'] )) {
            $q[] = "ID='" . $data['id'] . "'";
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping_inpost" . (!empty( $q ) ? " WHERE " . implode( " AND " , $q ) : ""));
        if ($query->num_rows) {
            foreach( $query->rows as $item )
            {
                $map[] = [
                    'id' => $item['ID'],
                    'description' => $item['LOCATION_DESCRIPTION'],
                    'street' => $item['STREET'] . ' ' . $item['BUILDING_NUMBER'],
                    'postcode' => $item['POST_CODE'],
                    'city' => $item['CITY'],
                    'state' => $item['PROVINCE'],
                    'lat' => $item['LATITUDE'],
                    'lng' => $item['LONGITUDE']
                ];

                $inpost_id = 'inpost_' . $item['ID'];
                $quote_data[$inpost_id] = array(
                    'code'         => 'inpost.' . $inpost_id,
                    'title'        => '(' . $item['ID'] . ') - ' . $item['STREET'] . ' ' . $item['BUILDING_NUMBER'] . ', ' . $item['CITY'] . '" class="hidden"',
                    'cost'         => $this->config->get('shipping_inpost_total'),
                    'tax_class_id' => $this->config->get('shipping_inpost_tax_class_id'),
                    'text'         => $this->currency->format($this->tax->calculate($this->config->get('shipping_inpost_total'), $this->config->get('shipping_inpost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
                );
            }
        }

        return $map;
    }

    public function generateQuote( $post )
    {
        $this->load->language('extension/shipping/inpost');

        if( empty( $post['id'] )) {
            $json = array('error' => true, 'msg' => 'Brak parametru POST id');
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping_inpost WHERE ID='" . $post['id'] . "'");
            if ($query->num_rows) {
                foreach( $query->rows as $item )
                {
                    $inpost_id = 'inpost_' . $item['ID'];
                    $quote_data[$inpost_id] = array(
                        'code'         => 'inpost.' . $inpost_id,
                        'title'        => '(' . $item['ID'] . ') - ' . $item['STREET'] . ' ' . $item['BUILDING_NUMBER'] . ', ' . $item['CITY'],
                        'cost'         => $this->config->get('shipping_inpost_total'),
                        'tax_class_id' => $this->config->get('shipping_inpost_tax_class_id'),
                        'text'         => $this->currency->format($this->tax->calculate($this->config->get('shipping_inpost_total'), $this->config->get('shipping_inpost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
                    );
                }

                //file_put_contents( DIR_APPLICATION . 'inpost.log' , print_r($this->session->data, true));
                $this->session->data['shipping_methods']['inpost'] = [
                    'title' => 'Paczkomaty Inpost',
                    'quote' => $quote_data,
                    'sort_order' => 0,
                    'error' => ''

                ];
                //file_put_contents( DIR_APPLICATION . 'inpost.log' , print_r($this->session->data['shipping_methods'], true));

                $json = array('success' => true);
            }
        }

        return json_encode( $json );
    }
}
