<?php
namespace Opencart\Admin\Controller\Extension\Payunipayment\Payment;
class Payunipayment extends \Opencart\System\Engine\Controller {

	private $version = '1.0';
	private $error = [];
    private $prefix;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'payment_' : '';
    }
	
	public function index(): void {
		$this->load->language('extension/payunipayment/payment/payunipayment');
		
		$this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->load->model('setting/setting');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payunipayment/payment/payunipayment', 'user_token=' . $this->session->data['user_token'])
		];
		
		$data['save'] = $this->url->link('extension/payunipayment/payment/payunipayment|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['text_info'] = sprintf($this->language->get('text_info'), $this->version);
		
		$data['server'] = HTTP_SERVER;
		$data['catalog'] = HTTP_CATALOG;

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->error['front_name'])) {
            $data['error_front_name'] = $this->error['front_name'];
        } else {
            $data['error_front_name'] = '';
        }
        if (isset($this->error['status'])) {
            $data['error_status'] = $this->error['status'];
        } else {
            $data['error_status'] = '';
        }
        if (isset($this->error['test_mode'])) {
            $data['error_test_mode'] = $this->error['test_mode'];
        } else {
            $data['error_test_mode'] = '';
        }
        if (isset($this->error['merchant_id'])) {
            $data['error_merchant_id'] = $this->error['merchant_id'];
        } else {
            $data['error_merchant_id'] = '';
        }
        if (isset($this->error['hash_key'])) {
            $data['error_hash_key'] = $this->error['hash_key'];
        } else {
            $data['error_hash_key'] = '';
        }
        if (isset($this->error['hash_iv'])) {
            $data['error_hash_iv'] = $this->error['hash_iv'];
        } else {
            $data['error_hash_iv'] = '';
        }
        if (isset($this->error['item_info'])) {
            $data['error_item_info'] = $this->error['item_info'];
        } else {
            $data['error_item_info'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
		
        $setting = $this->model_setting_setting->getSetting($this->prefix . 'payunipayment', $store_id);
        if (isset($this->request->post[$this->prefix . 'payunipayment_front_name'])) {
            $data[$this->prefix . 'payunipayment_front_name'] = $this->request->post[$this->prefix . 'payunipayment_front_name'];
        } else {
            $data[$this->prefix . 'payunipayment_front_name'] = isset($setting[$this->prefix . 'payunipayment_front_name']) ? $setting[$this->prefix . 'payunipayment_front_name'] : $this->language->get('heading_title');
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_status'])) {
            $data[$this->prefix . 'payunipayment_status'] = $this->request->post[$this->prefix . 'payunipayment_status'];
        } else {
            $data[$this->prefix . 'payunipayment_status'] = isset($setting[$this->prefix . 'payunipayment_status']) ? $setting[$this->prefix . 'payunipayment_status'] : 1;
        }

        if (isset($this->request->post[$this->prefix . 'payunipayment_test_mode'])) {
            $data[$this->prefix . 'payunipayment_test_mode'] = $this->request->post[$this->prefix . 'payunipayment_test_mode'];
        } else {
            $data[$this->prefix . 'payunipayment_test_mode'] = isset($setting[$this->prefix . 'payunipayment_test_mode']) ? $setting[$this->prefix . 'payunipayment_test_mode'] : 1;
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_merchant_id'])) {
            $data[$this->prefix . 'payunipayment_merchant_id'] = $this->request->post[$this->prefix . 'payunipayment_merchant_id'];
        } else {
            $data[$this->prefix . 'payunipayment_merchant_id'] = isset($setting[$this->prefix . 'payunipayment_merchant_id']) ? $setting[$this->prefix . 'payunipayment_merchant_id'] : '';
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_hash_key'])) {
            $data[$this->prefix . 'payunipayment_hash_key'] = $this->request->post[$this->prefix . 'payunipayment_hash_key'];
        } else {
            $data[$this->prefix . 'payunipayment_hash_key'] = isset($setting[$this->prefix . 'payunipayment_hash_key']) ? $setting[$this->prefix . 'payunipayment_hash_key'] : '';
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_hash_iv'])) {
            $data[$this->prefix . 'payunipayment_hash_iv'] = $this->request->post[$this->prefix . 'payunipayment_hash_iv'];
        } else {
            $data[$this->prefix . 'payunipayment_hash_iv'] = isset($setting[$this->prefix . 'payunipayment_hash_iv']) ? $setting[$this->prefix . 'payunipayment_hash_iv'] : '';
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_item_info'])) {
            $data[$this->prefix . 'payunipayment_item_info'] = $this->request->post[$this->prefix . 'payunipayment_item_info'];
        } else {
            $data[$this->prefix . 'payunipayment_item_info'] = isset($setting[$this->prefix . 'payunipayment_item_info']) ? $setting[$this->prefix . 'payunipayment_item_info'] : $this->language->get('text_item_info');
        }
        $this->load->model('localisation/order_status');

        $order_status_all = $this->model_localisation_order_status->getOrderStatuses(array());
        $data['order_status_all'] = array();


        if ($order_status_all) { 
            foreach ($order_status_all as $item) { 
                $data['order_status_all'][] = array(
                    'order_status_id' => $item['order_status_id'],
                    'name' => $item['name']
                );
            }
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_order_status'])) {
            $data[$this->prefix . 'payunipayment_order_status'] = $this->request->post[$this->prefix . 'payunipayment_order_status'];
        } else {
            $data[$this->prefix . 'payunipayment_order_status'] = isset($setting[$this->prefix . 'payunipayment_order_status']) ? $setting[$this->prefix . 'payunipayment_order_status'] : 1;
        }
        $this->load->model('localisation/order_status');

        $order_status_all = $this->model_localisation_order_status->getOrderStatuses(array());
        $data['order_status_all'] = array();


        if ($order_status_all) { 
            foreach ($order_status_all as $item) { 
                $data['order_status_all'][] = array(
                    'order_status_id' => $item['order_status_id'],
                    'name' => $item['name']
                );
            }
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_order_finish_status'])) {
            $data[$this->prefix . 'payunipayment_order_finish_status'] = $this->request->post[$this->prefix . 'payunipayment_order_finish_status'];
        } else {
            $data[$this->prefix . 'payunipayment_order_finish_status'] = isset($setting[$this->prefix . 'payunipayment_order_finish_status']) ? $setting[$this->prefix . 'payunipayment_order_finish_status'] : 15;
        }
        $this->load->model('localisation/order_status');

        $order_status_all = $this->model_localisation_order_status->getOrderStatuses(array());
        $data['order_status_all'] = array();


        if ($order_status_all) { 
            foreach ($order_status_all as $item) { 
                $data['order_status_all'][] = array(
                    'order_status_id' => $item['order_status_id'],
                    'name' => $item['name']
                );
            }
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_order_fail_status'])) {
            $data[$this->prefix . 'payunipayment_order_fail_status'] = $this->request->post[$this->prefix . 'payunipayment_order_fail_status'];
        } else {
            $data[$this->prefix . 'payunipayment_order_fail_status'] = isset($setting[$this->prefix . 'payunipayment_order_fail_status']) ? $setting[$this->prefix . 'payunipayment_order_fail_status'] : 10;
        }
        if (isset($this->request->post[$this->prefix . 'payunipayment_sort_order'])) {
            $data[$this->prefix . 'payunipayment_sort_order'] = $this->request->post[$this->prefix . 'payunipayment_sort_order'];
        } else {
            $data[$this->prefix . 'payunipayment_sort_order'] = isset($setting[$this->prefix . 'payunipayment_sort_order']) ? $setting[$this->prefix . 'payunipayment_sort_order'] : '';
        }   

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payunipayment/payment/payunipayment', $data));
	}
	
	public function save(): void {
		$this->load->language('extension/payunipayment/payment/payunipayment');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting($this->prefix . 'payunipayment', $this->request->post);
            $data['success'] = $this->language->get('text_success');
        }
		
		$data['error'] = $this->error;
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payunipayment/payment/payunipayment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post[$this->prefix . 'payunipayment_front_name'])) {
            $this->error['warning'] = $this->language->get('error_front_name');
        }
        if (empty($this->request->post[$this->prefix . 'payunipayment_merchant_id'])) {
            $this->error['warning'] = $this->language->get('error_merchant_id');
        }
        if (empty($this->request->post[$this->prefix . 'payunipayment_hash_key'])) {
            $this->error['warning'] = $this->language->get('error_hash_key');
        }
        if (empty($this->request->post[$this->prefix . 'payunipayment_hash_iv'])) {
            $this->error['warning'] = $this->language->get('error_hash_iv');
        }
        if (empty($this->request->post[$this->prefix . 'payunipayment_item_info'])) {
            $this->error['warning'] = $this->language->get('error_item_info');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

	public function install() {
	}
}