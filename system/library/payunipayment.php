<?php
namespace Opencart\System\Library;
class Payunipayment {

	private $error = array();
    private $prefix;
    private $config;
    private $configSetting = array();

	public function __construct($config) {

        $this->config = $config;
        $this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'payment_' : '';

        if ($this->config->get($this->prefix . 'payunipayment_status')) {
            $this->configSetting = [
                'front_name'          => $this->config->get($this->prefix . 'payunipayment_front_name'),
                'test_mode'           => $this->config->get($this->prefix . 'payunipayment_test_mode'),
                'merchant_id'         => $this->config->get($this->prefix . 'payunipayment_merchant_id'),
                'hash_key'            => $this->config->get($this->prefix . 'payunipayment_hash_key'),
                'hash_iv'             => $this->config->get($this->prefix . 'payunipayment_hash_iv'),
                'item_info'           => $this->config->get($this->prefix . 'payunipayment_item_info'),
                'order_status'        => $this->config->get($this->prefix . 'payunipayment_order_status'),
                'order_finish_status' => $this->config->get($this->prefix . 'payunipayment_order_finish_status'),
                'order_fail_status'   => $this->config->get($this->prefix . 'payunipayment_order_fail_status'),
                'sort_order'          => $this->config->get($this->prefix . 'payunipayment_sort_order'),
            ];
        }

	}

    public function getConfigSetting() {
    	return $this->configSetting;
    }

    /**
     * 產生訊息內容
     * return string
     */
    public function SetNotice(Array $encryptInfo) {
        $trdStatus = ['待付款','已付款','付款失敗','付款取消'];
        $message   = "<<<code>統一金流 PAYUNi</code>>>";
        switch ($encryptInfo['PaymentType']){
            case '1': // 信用卡
                $authType = [0=>'無', 1=>'一次', 2=>'分期', 3=>'紅利', 4=>'Apple Pay', 5=>'Google Pay', 6=>'Samsung Pay', 7=>'銀聯'];
                $encryptInfo['AuthType'] = (array_key_exists($encryptInfo['AuthType'], $authType)) ? $encryptInfo['AuthType'] : 0 ;
                $message .= "</br>授權狀態：" . $encryptInfo['Message'];
                $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                $message .= "</br>卡號：" . $encryptInfo['Card6No'] . '******' . $encryptInfo['Card4No'];
                if ($encryptInfo['CardInst'] > 1) {
                    $message .= "</br>分期數：" . $encryptInfo['CardInst'];
                    $message .= "</br>首期金額：" . $encryptInfo['FirstAmt'];
                    $message .= "</br>每期金額：" . $encryptInfo['EachAmt'];
                }
                $message .= "</br>授權碼：" . $encryptInfo['AuthCode'];
                $message .= "</br>授權銀行代號：" . $encryptInfo['AuthBank'];
                $message .= "</br>授權銀行：" . $encryptInfo['AuthBankName'];
                $message .= "</br>授權類型：" . $authType[$encryptInfo['AuthType']];
                $message .= "</br>授權日期：" . $encryptInfo['AuthDay'];
                $message .= "</br>授權時間：" . $encryptInfo['AuthTime'];
                break;
            case '2': // atm轉帳
                $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                $message .= "</br>銀行代碼：" . $encryptInfo['BankType'];
                $message .= "</br>繳費帳號：" . $encryptInfo['PayNo'];
                $message .= "</br>繳費截止時間：" . $encryptInfo['ExpireDate'];
                break;
            case '3': // 超商代碼
                $store = ['SEVEN' => '統一超商 (7-11)'];
                $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                $message .= "</br>繳費方式：" . $store[$encryptInfo['Store']];
                $message .= "</br>繳費代號：" . $encryptInfo['PayNo'];
                $message .= "</br>繳費截止時間：" . $encryptInfo['ExpireDate'];
                break;
            case '6': // ICP 愛金卡
                $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                $message .= "</br>愛金卡交易序號：" . $encryptInfo['PayNo'];
                $message .= "</br>付款日期時間：" . $encryptInfo['PayTime'];
                break;
            default: // 預設顯示資訊
                $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                break;
        }
        return $message;
    }

    /**
     * 處理api回傳的結果
     * @ author    Yifan
     * @ dateTime 2022-08-26
     */
    public function ResultProcess($result) {
        $msg = '';
        if (is_array($result)) {
            $resultArr = $result;
        }
        else {
            $resultArr = json_decode($result, true);
            if (!is_array($resultArr)){
                $msg = 'Result must be an array';
                $this->writeLog($msg);
                return ['success' => false, 'message' => $msg];
            }
        }
        if (isset($resultArr['EncryptInfo'])){
            if (isset($resultArr['HashInfo'])){
                $chkHash = $this->HashInfo($resultArr['EncryptInfo']);
                if ( $chkHash != $resultArr['HashInfo'] ) {
                    $msg = 'Hash mismatch';
                    $this->writeLog($msg);
                    return ['success' => false, 'message' => $msg];
                }
                $resultArr['EncryptInfo'] = $this->Decrypt($resultArr['EncryptInfo']);
                return ['success' => true, 'message' => $resultArr];
            }
            else {
                $msg = 'missing HashInfo';
                $this->writeLog($msg);
                return ['success' => false, 'message' => $msg];
            }
        }
        else {
            $msg = 'missing EncryptInfo';
            $this->writeLog($msg);
            return ['success' => false, 'message' => $msg];
        }
    }

	/**
     * 加密
     */
    public function Encrypt($encryptInfo) {
        $tag = '';
        $encrypted = openssl_encrypt(http_build_query($encryptInfo), 'aes-256-gcm', trim($this->configSetting['hash_key']), 0, trim($this->configSetting['hash_iv']), $tag);
        return trim(bin2hex($encrypted . ':::' . base64_encode($tag)));
    }

    /**
     * 解密
     */
    public function Decrypt(string $encryptStr = '') {
        list($encryptData, $tag) = explode(':::', hex2bin($encryptStr), 2);
        $encryptInfo = openssl_decrypt($encryptData, 'aes-256-gcm', trim($this->configSetting['hash_key']), 0, trim($this->configSetting['hash_iv']), base64_decode($tag));
        parse_str($encryptInfo, $encryptArr);
        return $encryptArr;
    }

    /**
     * hash
     */
    public function HashInfo(string $encryptStr = '') {
        return strtoupper(hash('sha256', $this->configSetting['hash_key'].$encryptStr.$this->configSetting['hash_iv']));
    }

    /**
     * log
     */
    public function writeLog($msg = '', $with_input = true)
    {
        $file_path = DIR_LOGS; // 檔案路徑
        if(! is_dir($file_path)) {
            return;
        }

        $file_name = 'payuni_' . date('Ymd', time()) . '.txt';  // 取時間做檔名 (YYYYMMDD)
        $file = $file_path . $file_name;
        $fp = fopen($file, 'a');
        $input = ($with_input) ? '|REQUEST:' . json_encode($_REQUEST) : '';
        $log_str = date('Y-m-d h:i:s') . '|' . $msg . $input . "\n";
        fwrite($fp, $log_str);
        fclose($fp);
    }
}