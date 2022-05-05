<?php
class ControllerExtensionPaymentfastpgesun extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/fastpgesun');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_fastpgesun', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}

		if (isset($this->error['security'])) {
			$data['error_security'] = $this->error['security'];
		} else {
			$data['error_security'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/fastpgesun', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/fastpgesun', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_fastpgesun_apiKey'])) {
			$data['payment_fastpgesun_apiKey'] = $this->request->post['payment_fastpgesun_apiKey'];
		} else {
			$data['payment_fastpgesun_apiKey'] = $this->config->get('payment_fastpgesun_apiKey');
		}

		if (isset($this->request->post['payment_fastpgesun_apiSecret'])) {
			$data['payment_fastpgesun_apiSecret'] = $this->request->post['payment_fastpgesun_apiSecret'];
		} else {
			$data['payment_fastpgesun_apiSecret'] = $this->config->get('payment_fastpgesun_apiSecret');
		}

		$data['callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/fastpgesun/callback';

		if (isset($this->request->post['payment_fastpgesun_status'])) {
			$data['payment_fastpgesun_status'] = $this->request->post['payment_fastpgesun_status'];
		} else {
			$data['payment_fastpgesun_status'] = $this->config->get('payment_fastpgesun_status');
		}

		if (isset($this->request->post['payment_fastpgesun_sort_order'])) {
			$data['payment_fastpgesun_sort_order'] = $this->request->post['payment_fastpgesun_sort_order'];
		} else {
			$data['payment_fastpgesun_sort_order'] = $this->config->get('payment_fastpgesun_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/fastpgesun', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/fastpgesun')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_fastpgesun_apiKey']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['payment_fastpgesun_apiSecret']) {
			$this->error['security'] = $this->language->get('error_security');
		}

		return !$this->error;
	}
}