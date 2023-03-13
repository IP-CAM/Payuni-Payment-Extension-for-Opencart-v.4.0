<?php
namespace Opencart\Catalog\Controller\Extension\Payunipayment\Payment;
class Payunipayment extends \Opencart\System\Engine\Controller {

    private $error = array();
    private $configSetting = array();
    private $payunipayment;

    public function __construct($registry) {
        parent::__construct($registry);

		if (version_compare(phpversion(), '7.1', '>=')) {
			ini_set('precision', 14);
			ini_set('serialize_precision', 14);
		}

        // library
        $_config = new \Opencart\System\Engine\Config();
        $_config->addPath(DIR_EXTENSION . 'payunipayment/system/config/');
        $_config->load('payunipayment');
        require_once DIR_EXTENSION . 'payunipayment/system/library/payunipayment.php';
        $this->payunipayment = new \Opencart\System\Library\Payunipayment($this->config);

        $this->configSetting = $this->payunipayment->getConfigSetting();
    }
	
	public function index(): string {

        // Test Mode
        if ($this->configSetting['test_mode'] == 1) {
            $data['action'] = "https://sandbox-api.payuni.com.tw/api/upp"; //測試網址
        } else {
            $data['action'] = "https://api.payuni.com.tw/api/upp"; // 正式網址
        }

        $data['params'] = $this->uppOnePointHandler();
        $data['item_info'] = $this->configSetting['item_info'];

        return $this->load->view('extension/payunipayment/payment/payunipayment', $data);
	}

    public function confirm() {
        $json = array();
            if ($this->session->data['payment_method'] == 'payunipayment') {
                $this->load->model('checkout/order');
                $this->model_checkout_order->addHistory($this->session->data['order_id'], $this->configSetting['order_status']);
                $json['redirect'] = $this->url->link('checkout/success');
            }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     *upp資料處理
     *
     * @access private
     * @version 1.0
     * @return array
     */
    private function uppOnePointHandler() {
        // 訂單資料
        $orderInfo    = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        // 商品資料
        $productsInfo = $this->model_checkout_order->getProducts($this->session->data['order_id']);
        $prodDesc     = [];
        foreach ($productsInfo as $product) {
            $prodDesc[] = $product['name'] . ' * ' . $product['quantity'];
        }

        $encryptInfo = [
            'MerID'      => $this->configSetting['merchant_id'],
            'MerTradeNo' => $orderInfo['order_id'],
            'ProdDesc'   => implode(';', $prodDesc),
            'TradeAmt'   => (int) $orderInfo['total'],
            'ReturnURL'  => $this->url->link('extension/payunipayment/checkout/returninfo'), //幕前
            'NotifyURL'  => $this->url->link('extension/payunipayment/checkout/notify'), //幕後
            'UsrMail'    => (isset($orderInfo['email'])) ? $orderInfo['email'] : '',
            'Timestamp'  => time()
        ];
        $parameter['MerID']       = $this->configSetting['merchant_id'];
        $parameter['Version']     = '1.0';
        $parameter['EncryptInfo'] = $this->payunipayment->Encrypt($encryptInfo);
        $parameter['HashInfo']    = $this->payunipayment->HashInfo($parameter['EncryptInfo']);
        return $parameter;
    }
}