<?php
namespace Opencart\Catalog\Controller\Extension\Payunipayment\Checkout;
class Notify extends \Opencart\System\Engine\Controller {

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

        if ($result['success'] == false) {
            $this->payunipayment->writeLog("解密失敗");
            exit();
        }

        if ($result['message']['Status'] != 'SUCCESS') {
            $this->payunipayment->writeLog("交易失敗：" . $result['message']['Status'] . "(" . $result['message']['EncryptInfo']['Message'] . ")");
            exit();
        }

        // 取得該筆交易資料
        $encryptInfo = $result['message']['EncryptInfo'];
        $this->load->model('checkout/order');
        $orderInfo = $this->model_checkout_order->getOrder($encryptInfo['MerTradeNo']);

        // 1. 檢查訂單是否存在
        if (!$orderInfo) {
            $this->payunipayment->writeLog("取得訂單失敗，訂單編號：" . $encryptInfo['MerTradeNo']);
            exit();
        }

        // 2. 檢查交易總金額
        if (intval($orderInfo['total']) != $encryptInfo['TradeAmt']) {
            $msg = "錯誤: 結帳金額與訂單金額不一致";

            // 更新訂單狀態並寫入訂單歷程
            $this->model_checkout_order->addHistory(
                $orderInfo['order_id'],
                $this->configSetting['order_fail_status'],
                $msg,
                true
            );

            $this->payunipayment->writeLog($msg);
            exit();
        }

        // 3. 檢查訂單狀態
        switch ($encryptInfo['TradeStatus']) {
            case '0':
                $msg = $orderInfo['order_id'] . ' Pending';  //訂單未付款

                // 訂單未付款，更新訂單狀態並寫入訂單歷程
                $this->model_checkout_order->addHistory(
                    $orderInfo['order_id'],
                    2, // 待付款
                    $this->payunipayment->SetNotice($encryptInfo),
                    true
                );
                break;
            case '1':
                $msg = $orderInfo['order_id'] . ' OK';  //訂單成功

                // 已付款，更新訂單狀態並寫入訂單歷程
                $this->model_checkout_order->addHistory(
                    $orderInfo['order_id'],
                    $this->configSetting['order_finish_status'],
                    $this->payunipayment->SetNotice($encryptInfo),
                    true
                );
                break;
            default:
                $msg = '';
                break;
        }

        $this->payunipayment->writeLog($msg);
        exit(true);
	}
}