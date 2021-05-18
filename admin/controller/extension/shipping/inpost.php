<?php
/**
 *  Avatec Inpost Integration
 *  Copyright (c) 2020 Grzegorz Miskiewicz
 *  All Rights Reserved
 */


class ControllerExtensionShippingInpost extends Controller {

    private $error = [];

    public function install()
    {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shipping_inpost` (
			  `ID` varchar(25) NOT NULL,
			  `TYPE` varchar(25) NOT NULL,
			  `POST_CODE` varchar(10) NOT NULL,
			  `PROVINCE` varchar(25) NOT NULL,
			  `STREET` varchar(25) NOT NULL,
			  `BUILDING_NUMBER` varchar(50) NOT NULL,
			  `CITY` varchar(25) NOT NULL,
			  `LATITUDE` float NOT NULL,
			  `LONGITUDE` float NOT NULL,
			  `PAYMENT_AVAILABLE` varchar(10) NOT NULL,
			  `STATUS` varchar(10) NOT NULL,
			  `LOCATION_DESCRIPTION` varchar(100) NOT NULL,
			  `LOCATION_DESCRIPTION2` varchar(100) NOT NULL,
			  `OPERATINGHOURS` varchar(50) NOT NULL,
			  `PARTNER_ID` varchar(25) NOT NULL,
			  `PAYMENT_TYPE` int(10) NOT NULL,
			  KEY `PROVINCE` (`PROVINCE`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
		");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "shipping_inpost` ADD FULLTEXT( `CITY`);");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "shipping_inpost`");
    }

    public function index()
    {
        $this->load->language('extension/shipping/inpost');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_inpost', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

        if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        $data['user_token'] = $this->session->data['user_token'];

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
    			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
    			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
    			'href' => $this->url->link('extension/shipping/inpost', 'user_token=' . $this->session->data['user_token'], true)
            ]
        ];

        $data['action'] = $this->url->link('extension/shipping/inpost', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);
        $data['search_url'] = $this->url->link('extension/shipping/inpost/search', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['shipping_inpost_total'])) {
			$data['shipping_inpost_total'] = $this->request->post['shipping_inpost_total'];
		} else {
			$data['shipping_inpost_total'] = $this->config->get('shipping_inpost_total');
		}

        if (isset($this->request->post['shipping_inpost_google_api_key'])) {
            $data['shipping_inpost_google_api_key'] = $this->request->post['shipping_inpost_google_api_key'];
        } else {
            $data['shipping_inpost_google_api_key'] = $this->config->get('shipping_inpost_google_api_key');
        }

        $this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['shipping_inpost_geo_zone_id'])) {
			$data['shipping_inpost_geo_zone_id'] = $this->request->post['shipping_inpost_geo_zone_id'];
		} else {
			$data['shipping_inpost_geo_zone_id'] = $this->config->get('shipping_inpost_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_inpost_status'])) {
			$data['shipping_inpost_status'] = $this->request->post['shipping_inpost_status'];
		} else {
			$data['shipping_inpost_status'] = $this->config->get('shipping_inpost_status');
		}


        if (isset($this->request->post['shipping_inpost_type'])) {
            $data['shipping_inpost_type'] = $this->request->post['shipping_inpost_type'];
        } else {
            $data['shipping_inpost_type'] = $this->config->get('shipping_inpost_type');
        }

		if (isset($this->request->post['shipping_inpost_sort_order'])) {
			$data['shipping_inpost_sort_order'] = $this->request->post['shipping_inpost_sort_order'];
		} else {
			$data['shipping_inpost_sort_order'] = $this->config->get('shipping_inpost_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

        if (!empty($this->request->get['filter_postcode'])) {
            $filter['postcode'] = "POST_CODE='" . $this->request->get['filter_postcode'] . "'";
            $data['filter_postcode'] = $this->request->get['filter_postcode'];
        }

        if (!empty($this->request->get['filter_city'])) {
            $filter['city'] = "CITY = '" . $this->request->get['filter_city'] . "'";
            $data['filter_city'] = $this->request->get['filter_city'];
        }

        if( !empty( $filter )) {
            $query = "WHERE " . implode( " AND " , $filter );
        }

        $inpost_list = $this->db->query("SELECT * FROM `" . DB_PREFIX . "shipping_inpost` " . (!empty( $query) ? $query : "") . " ORDER BY CITY");
        $history_total = $inpost_list->num_rows;
        $data['inpost_list'] = $inpost_list->rows;

        if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

        $url = '';
        if (isset($this->request->get['page'])) {
            $url = '&page=' . $this->request->get['page'];
        }

        $pagination = new Pagination();
        $pagination->total = $history_total;
        $pagination->page = (!empty( $page ) ? $page : 1);
        $pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/shipping/inpost', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

        if( empty( $page ) || $page == 1 ) {
            $from_row = 0;
            $to_row = $from_row + $pagination->limit - 1;
        } else {
            $from_row = ($page - 1) * $pagination->limit;
            $to_row = $from_row + $pagination->limit;
        }

        for($i=0; $i<$history_total; $i++ ) {

            if( $page == 1 && $i>$to_row ) {
                unset( $data['inpost_list'][$i] );
            }

            if( $page > 1 ) {
                if( $i < $from_row ) {
                    unset( $data['inpost_list'][$i] );
                }

                if( $i > $to_row ) {
                    unset( $data['inpost_list'][$i] );
                }
            }
        }

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('extension/shipping/inpost', $data));
    }

    public function refresh()
    {
        ini_set('display_errors' , 1);
        error_reporting(E_ALL);
        header('Content-type: application/json');

        $this->load->language('extension/shipping/inpost');

        $inpost_url = 'http://api.paczkomaty.pl/?do=listmachines_xml';
        $inpost_file = __DIR__ . '/inpost.xml';

        if( file_exists( $inpost_file ) == false ) {
            $this->copy_file( $inpost_url , $inpost_file);
        } elseif ( filemtime( $inpost_file ) > (time() + (3600 * 24)) ) {
            $this->copy_file( $inpost_url , $inpost_file);
        } else {
            die(json_encode(['success' => true, 'msg' => '<i class="fa fa-info fa-fw"></i> Lista paczkomatów jest aktualna !']));
        }

        $sql_columns = array('name','type','postcode','province','street','buildingnumber','town','latitude','longitude','paymentavailable','status','locationdescription','operatinghours','paymentpointdescr','partnerid','paymenttype');

        $this->db->query("DELETE FROM `" . DB_PREFIX . "shipping_inpost`");

        $xml = simplexml_load_file( $inpost_file );
        foreach( $xml->machine as $i ) {
            foreach( $sql_columns as $col ) {
                $query[] = "'" . $this->db->escape( $i->{$col}[0] ) . "'";
            }

            try {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "shipping_inpost` VALUES(" . implode("," , $query) . ")");
            } catch( Error $e ) {
                die(json_encode(['error' => true, 'msg' => '<i class="fa fa-times fa-fw"></i> Wystąpił błąd w bazie danych: ' . $this->db->error]));
            }

            if( !empty( $this->db->errno )) {
                die(json_encode(['error' => true, 'msg' => '<i class="fa fa-times fa-fw"></i> Wystąpił błąd w bazie danych: ' . $this->db->error]));
            }

            unset($query);
        }

        die(json_encode(['success' => true, 'msg' => '<i class="fa fa-check fa-fw"></i> Import zakończony pomyślnie. Za chwilę zostanie załadowana lista paczkomatów.']));
    }

    protected function copy_file($src, $dst)
    {
        $this->load->language('extension/shipping/inpost');

        try {
            copy( $src , $dst);
        } catch( Error $e ) {
            die(json_encode(['error' => true, 'msg' => $this->language->get('api_copy_error') ]));
        } finally {
            chmod( $dst , 0755 );
            return true;
        }
    }

    protected function validate()
    {
		if (!$this->user->hasPermission('modify', 'extension/shipping/inpost')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
