<?php

namespace BCLPaymentLink;

defined('ABSPATH') || exit;

class BCLOrderColumn {
	private const COLUMN_NAME = 'bcl_payment_link';
	private const META_KEY = '_bcl_payment_link';

	public function __construct() {
		$this->init_hooks();
	}

	private function init_hooks(): void {
		// Non-HPOS hooks
		add_filter('manage_edit-shop_order_columns', [$this, 'add_custom_column']);
		add_action('manage_shop_order_posts_custom_column', [$this, 'populate_custom_column'], 10, 2);

		// HPOS hooks
		add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'add_custom_column']);
		add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'populate_custom_column_hpos'], 10, 2);

		// AJAX action for button click
		add_action('wp_ajax_bcl_generate_payment_link', [$this, 'generate_payment_link']);
	}

	public function add_custom_column(array $columns): array {
		$insert_position = array_search('order_status', array_keys($columns)) ?: 0;
		return array_slice($columns, 0, $insert_position + 1, true) +
		       [self::COLUMN_NAME => __('BCL Payment Link', 'bcl-payment-link')] +
		       array_slice($columns, $insert_position + 1, null, true);
	}

	public function populate_custom_column($column, $post_id): void {
		if (self::COLUMN_NAME === $column) {
			$this->display_bcl_payment_link(wc_get_order($post_id));
		}
	}

	public function populate_custom_column_hpos($column, $order): void {
		if (self::COLUMN_NAME === $column) {
			$this->display_bcl_payment_link($order);
		}
	}

	private function display_bcl_payment_link($order): void {
		if ($order->is_paid()) {
			$this->display_order_paid($order);
			return;
		}

		$order_id = $order->get_id();
		$payment_link = $order->get_meta(self::META_KEY);

		echo '<div class="bcl-payment-link-section">';

		if ($payment_link) {
			printf(
				'<button class="button bcl-copy-payment-link" data-payment-link="%s">%s</button>',
				esc_attr($payment_link),
				esc_html__('Copy Link', 'bcl-payment-link')
			);
		} else {
			printf(
				'<button class="button bcl-generate-payment-link" data-order-id="%d">%s</button>',
				esc_attr($order_id),
				esc_html__('Generate Payment Link', 'bcl-payment-link')
			);
		}
		printf('<div id="bcl-payment-link-%d" class="bcl-payment-link-result"></div>', esc_attr($order_id));

		echo '</div>';
	}

	private function display_order_paid($order): void {
		$payment_link = $order->get_meta(self::META_KEY);
		$order_status = $order->get_status();

		if (($order_status === 'completed' || $order_status === 'processing') && $payment_link) {
			echo '<div class="bcl-order-paid">' . esc_html__('Order Paid via BCL', 'bcl-payment-link') . '</div>';
		} else {
			echo '<div class="bcl-order-paid">' . esc_html__('Order Paid', 'bcl-payment-link') . '</div>';
		}
	}

	public function generate_payment_link(): void {
		check_ajax_referer('bcl_nonce', 'nonce');

		if (!current_user_can('edit_shop_orders')) {
			wp_send_json_error(__('You do not have permission to perform this action', 'bcl-payment-link'));
			return;
		}

		$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
		if (!$order_id) {
			wp_send_json_error(__('Invalid order ID', 'bcl-payment-link'));
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			wp_send_json_error(__('Order not found', 'bcl-payment-link'));
			return;
		}

		if ($order->is_paid()) {
			wp_send_json_error(__('This order has already been paid', 'bcl-payment-link'));
			return;
		}

		$api_response = $this->send_api_request($order);

		if (isset($api_response['success']) && $api_response['success']) {
			$payment_link = sanitize_text_field($api_response['data']['payment_link']);
			$order->update_meta_data(self::META_KEY, $payment_link);

			$order->add_order_note( // Translators: %s is the URL of the generated payment link
				sprintf(__('BCL payment link generated: %s', 'bcl-payment-link'), esc_url($payment_link)),
				false,
				true
			);

			$order->save();
			wp_send_json_success([
				'payment_link' => esc_url($payment_link),
				'message' => __('Payment link generated successfully', 'bcl-payment-link')
			]);
		} else {
			$error_message = $api_response['message'] ?? __('Failed to generate payment link', 'bcl-payment-link');
			wc_get_logger()->error('BCL Payment Link generation failed: ' . $error_message, ['source' => 'bcl-payment-link']);
			wp_send_json_error($error_message);
		}
	}

	private function send_api_request($order): array {
		$logger = wc_get_logger();
		$context = ['source' => 'bcl-payment-link'];

		$payload = [
			'amount' => $order->get_total(),
			'payer_name' => $this->sanitize_name($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
			'payer_email' => $order->get_billing_email(),
			'payer_telephone_number' => $this->sanitize_phone_number($order->get_billing_phone()),
			'portal_key' => get_option('bcl_portal_key', ''),
			'payment_channel' => get_option('bcl_payment_select', '1'),
			'order_number' => get_option('bcl_order_prefix', '') . '-' . $order->get_order_number()
		];

		$logger->info('Sending API request with payload: ' . wc_print_r($payload, true), $context);

		$response = wp_remote_post('https://bcl.my/api/payment-link', [
			'body' => $payload,
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . get_option('bcl_api_token', '')
			],
		]);

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			$logger->error('API request failed: ' . $error_message, $context);
			return [
				'success' => false,
				'message' => $error_message
			];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (!$data) {
			$logger->error('Failed to parse API response: ' . $body, $context);
			return [
				'success' => false,
				'message' => 'Failed to parse API response'
			];
		}

		$logger->info('API response received: ' . wc_print_r($data, true), $context);

		return $data;
	}

	private function sanitize_phone_number(string $phone): string {
		$sanitized = preg_replace('/[^\d]/', '', $phone);

		$sanitized = preg_replace('/^6/', '', $sanitized);

		$logger = wc_get_logger();
		$logger->warning("BCL Payment Link: Invalid phone number format: " . $phone, ['source' => 'bcl-payment-link']);

		return $sanitized;
	}

	private function sanitize_name(string $name): string {
		return trim(preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $name));
	}
}

// Initialize the class
new BCLOrderColumn();