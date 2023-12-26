<?php

namespace Opencart\Catalog\Model\Extension\Payunipayment\Payment;

class Payunipayment extends \Opencart\System\Engine\Model
{

	private $error = array();
	private $prefix;

	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'payment_' : '';
	}

	public function getMethods(array $address = []): array
	{
		$this->load->language('extension/payunipayment/payment/payunipayment');

		if ($this->cart->hasShipping()) {
			$country_id = 0;
			$zone_id    = 0;
			if (isset($address['country_id'])) {
				$country_id = $address['country_id'];
			} elseif (isset($this->session->data['shipping_address']['country_id'])) {
				$country_id = $this->session->data['shipping_address']['country_id'];
			}
			if (isset($address['zone_id'])) {
				$zone_id = $address['zone_id'];
			} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
				$zone_id = $this->session->data['shipping_address']['zone_id'];
			}
		}
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payunipayment_geo_zone_id') . "' AND country_id = '" . intval($country_id) . "' AND (zone_id = '" . intval($zone_id) . "' OR zone_id = '0')");

		if ($this->cart->hasSubscription()) {
			$status = false;
		} elseif (!$this->config->get('payunipayment_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {

			$option_data['payunipayment'] = [
				'code' => 'payunipayment.payunipayment',
				'name' => $this->language->get('text_title')
			];

			$method_data = [
				'code'       => 'payunipayment',
				'name'       => $this->language->get('text_title'),
				'option'     => $option_data,
				'sort_order' => $this->config->get($this->prefix . 'payunipayment_sort_order')
			];
		}

		return $method_data;
	}
}
