<?php
function curlOpenURL($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
    curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT,5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_exec($ch);
    return curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

class Currency {

    public $EUR;
    public $GBP;

    public function __construct($currencyURL_A, $currencyURL_B){

        if (curlOpenURL($currencyURL_A) == '200'){

            $content = file_get_contents($currencyURL_A);
            $aryCurrency = json_decode($content,true);
            $aryCurrencyEUR = $aryCurrency['USDEUR'];
            $aryCurrencyGBP = $aryCurrency['USDGBP'];
            $aryCurrencyTWD = $aryCurrency['USDTWD'];

            $this->EUR = 1 / $aryCurrencyEUR['Exrate'] * $aryCurrencyTWD['Exrate'];
            $this->GBP = 1 / $aryCurrencyGBP['Exrate'] * $aryCurrencyTWD['Exrate'];

        }else if (curlOpenURL($currencyURL_B) == '200'){

            $content = file_get_contents($currencyURL_B);
            $aryCurrency = json_decode($content,true);
            $this->EUR = $aryCurrency['EURTWD'];
            $this->GBP = $aryCurrency['GBPTWD'];

        }else{

            $this->EUR = '33.9228';
            $this->GBP = '38.3016';

        }
    }
}

class ControllerExtensionPaymentfastpgesun extends Controller {
	public function index() {
		$currencyURL_A = 'https://tw.rter.info/capi.php';
		$currencyURL_B = 'http://zellzone.com/currency.php';

		$currency = new Currency($currencyURL_A, $currencyURL_B);

		$data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['action'] = 'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014s';

		$apiKey = $this->config->get('payment_fastpgesun_apiKey');
        $apiSecret = $this->config->get('payment_fastpgesun_apiSecret');

		$currency_value = $currency->EUR;
		if($order_info['currency_code'] == 'GBP'){
			$currency_value = $currency->GBP;
		}

		$amount = number_format($order_info['total']*$order_info['currency_value']*$currency_value,0,".","");

		$TID = 'EC000001';  //終端機代號 (一般交易:EC000001、分期交易:EC000002)
		$ONO = $this->session->data['order_id'];  //訂單編號
		$TA = $amount;   //交易金額
		$U = str_replace("index.php?route=","catalog\/controller\/",$this->url->link('extension/payment/fastpgesun_callback.php', '', true));   //回覆位址

		$aryData['ONO'] = $ONO;
		$aryData['U'] = $U;
		$aryData['MID'] = $apiSecret;
		$aryData['TA'] = $TA;
		$aryData['TID'] = $TID;

		$dataJson = json_encode($aryData,true);
		$dataJson = str_replace('\\','',$dataJson);
		$mac = hash('sha256', $dataJson.$apiKey);

        $data['data'] = $dataJson;
        $data['mac'] = $mac;
        $data['ksn'] = '1';
		

		return $this->load->view('extension/payment/fastpgesun', $data);
	}

	public function callback() {
		$this->load->model('checkout/order');

		// echo json_encode($_POST, true);

		if($_POST != ''){
			if($_POST['RC'] == '00'){
			    //success
				$this->model_checkout_order->addOrderHistory($_POST['ONO'], 2);
				echo $this->url->link('checkout/success');
			}else if($_POST['RC'] == 'GR'){
			    //canceled
				$this->model_checkout_order->addOrderHistory($_POST['ONO'], 0);
				echo $this->url->link('checkout/failure');
			}else{
			    //pendding
				$this->model_checkout_order->addOrderHistory($_POST['ONO'], 0);
				echo $this->url->link('checkout/failure');
			}
		}else{
			echo $this->url->link('checkout/failure');
		}
	}
}
