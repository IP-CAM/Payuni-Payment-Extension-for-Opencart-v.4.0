<?php
namespace Opencart\Catalog\Controller\Extension\Payunipayment\Checkout;
class ReturnInfo extends \Opencart\System\Engine\Controller {

    private $error = array();
    private $configSetting = array();
    private $payunipayment;

    public function __construct($registry) {
        parent::__construct($registry);
        // library
        $_config = new \Opencart\System\Engine\Config();
        $_config->addPath(DIR_EXTENSION . 'payunipayment/system/config/');
        $_config->load('payunipayment');
        require_once DIR_EXTENSION . 'payunipayment/system/library/payunipayment.php';
        $this->payunipayment = new \Opencart\System\Library\Payunipayment($this->config);

        $this->configSetting = $this->payunipayment->getConfigSetting();
    }

    public function index(): void {

        // 交易結果
        $result = $this->payunipayment->ResultProcess($_POST);

        $encryptInfo = $result['message']['EncryptInfo'];

        /**
         * 頁面資料
         */
        $this->language->load('checkout/success');
        $this->language->load('checkout/success_error');

        if (isset($this->session->data['order_id'])) {
            $this->cart->clear();
            unset($this->session->data['order_id']);
            unset($this->session->data['payment_address']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['shipping_address']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['comment']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
        }

        // 顯示的 Title
        $title = ($encryptInfo['Status'] == 'SUCCESS') ? $this->language->get('heading_title') : $this->language->get('heading_title_fail') . $encryptInfo['Message'];

        $this->document->setTitle($title);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/home'),
            'text' => $this->language->get('text_home'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/cart'),
            'text' => $this->language->get('text_basket'),
            'separator' => $this->language->get('text_separator')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/checkout', '', 'SSL'),
            'text' => $this->language->get('text_checkout'),
            'separator' => $this->language->get('text_separator')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/success'),
            'text' => $this->language->get('text_success'),
            'separator' => $this->language->get('text_separator')
        );

        $data['text_message'] = $this->payunipayment->SetNotice($encryptInfo);

        $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['heading_title']  = $title;

        $this->response->setOutput($this->load->view('common/success', $data));
	}
}