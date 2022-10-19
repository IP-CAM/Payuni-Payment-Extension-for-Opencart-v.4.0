<?php
namespace Opencart\Catalog\Model\Extension\Payunipayment\Payment;
class Payunipayment extends \Opencart\System\Engine\Model {

    private $error = array();
    private $prefix;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'payment_' : '';
    }

	public function getMethod(array $address): array {
		$this->load->language('extension/payunipayment/payment/payunipayment');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payunipayment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

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
            $title = $this->config->get($this->prefix . 'payunipayment_title');
            $method_data = array(
                'code'       => 'payunipayment',
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get($this->prefix . 'payunipayment_sort_order')
            );
        }

		return $method_data;
	}
}