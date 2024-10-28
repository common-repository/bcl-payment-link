<?php

namespace BCLPaymentLink;

defined('ABSPATH') || exit;

class BCLCron {
	public function __construct() {
		add_filter('cron_schedules', [$this, 'add_cron_interval']);
		add_action('wp', [$this, 'schedule_event']);
		add_action('bcl_check_payment_status', [$this, 'check_payment_status']);
	}

	public function add_cron_interval($schedules) {
		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => __('Every Minute', 'bcl-payment-link')
		);
		return $schedules;
	}

	public function schedule_event(): void {
		if (!wp_next_scheduled('bcl_check_payment_status')) {
			wp_schedule_event(time(), 'every_minute', 'bcl_check_payment_status');
		}
	}

	public function check_payment_status(): void {
		$logger = wc_get_logger();
		$context = ['source' => 'bcl-payment-link-cron'];

		$logger->info('Starting payment status check', $context);

		$orders = wc_get_orders([
			'status' => ['pending', 'on-hold', 'cancelled', 'failed'],
			'limit' => 50,
			'orderby' => 'date',
			'order' => 'DESC',
			'meta_key' => '_bcl_payment_link', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_compare' => 'EXISTS',
		]);

		$logger->info('Found ' . count($orders) . ' orders to process', $context);

		foreach ($orders as $order) {
			$order_id = $order->get_id();
			$payment_link = $order->get_meta('_bcl_payment_link');

			$logger->info("Processing order #$order_id", $context);

			if (!$payment_link) {
				$logger->info("Order #$order_id: No payment link found, skipping", $context);
				continue;
			}

			$logger->info("Order #$order_id: Checking payment link status", $context);
			$status = $this->check_payment_link_status($order);

			$logger->info("Order #$order_id: Payment link status - $status", $context);

			if ($status === 'paid') {
				$order->update_status('processing', __('Payment received via BCL Payment Link', 'bcl-payment-link'));
				$logger->info("Order #$order_id marked as processing", $context);
			} elseif ($status === 'cancelled') {
				$order->update_status('cancelled', __('Payment cancelled', 'bcl-payment-link'));
				$logger->info("Order #$order_id cancelled", $context);
			} elseif ($status === 'failed') {
				$order->update_status('failed', __('Payment failed', 'bcl-payment-link'));
				$logger->info("Order #$order_id failed", $context);
			} else {
				$logger->info("Order #$order_id: No status change (status: $status)", $context);
			}
		}

		$logger->info('Finished payment status check', $context);
	}

	private function check_payment_link_status($order): string {
		$logger = wc_get_logger();
		$context = ['source' => 'bcl-payment-link-status-check'];

		$api_token = get_option('bcl_api_token', '');
		$order_prefix = get_option('bcl_order_prefix', '');
		$order_number = $order_prefix . '-' . $order->get_order_number();

		if (empty($api_token)) {
			$logger->error("API token is not set", $context);
			return 'error';
		}

		$api_url = 'https://bcl.my/api/transaction/' . $order_number;
		$logger->info("API URL: $api_url", $context);

		$args = [
			'headers' => [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . $api_token,
			],
		];

		$logger->info("Checking status for order: $order_number", $context);

		$response = wp_remote_get($api_url, $args);

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			$logger->error("Failed to check status: $error_message", $context);
			return 'error';
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$logger->error("Failed to parse API response", $context);
			return 'error';
		}

		if (!isset($data['data']['transaction']['status'])) {
			$logger->error("API response does not contain status field", $context);
			return 'error';
		}

		$status_code = $data['data']['transaction']['status'];
		$status = $this->interpret_status_code($status_code);

		$logger->info("Status for order $order_number: $status (code: $status_code)", $context);

		return $status;
	}

	private function interpret_status_code($code): string {
		switch ($code) {
			case '0':
				return 'new';
			case '1':
				return 'pending';
			case '2':
				return 'failed';
			case '3':
				return 'paid';
			case '4':
				return 'cancelled';
			default:
				return 'unknown';
		}
	}
}

// Initialize the cron job
new BCLCron();