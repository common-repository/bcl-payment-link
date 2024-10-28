<?php
/**
 * BCL Payment Link
 *
 * @package     BCLPaymentLink
 * @author      Web Impian Sdn Bhd
 * @license     GPLv3
 * @link        https://bayarcash.com
 *
 * @wordpress-plugin
 * Plugin Name:         BCL Payment Link
 * Plugin URI:          https://bcl.my
 * Description:         Generate BCL payment links for WordPress, with initial support for WooCommerce orders.
 * Version:             1.0.0
 * Author:              Web Impian Sdn Bhd
 * Author URI:          https://webimpian.com
 * Requires at least:   5.6
 * Tested up to:        6.6.1
 * Requires PHP:        7.4
 * License:             GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         bcl-payment-link
 * Domain Path:         /languages
 * Requires Plugins:    woocommerce
 */

namespace BCLPaymentLink;

defined('ABSPATH') || exit;

if (!defined('BCL_PAYMENT_LINK')) {
	define('BCL_PAYMENT_LINK', [
		'SLUG'     => 'bcl-payment-link',
		'FILE'     => __FILE__,
		'HOOK'     => plugin_basename(__FILE__),
		'PATH'     => plugin_dir_path(__FILE__),
		'URL'      => plugin_dir_url(__FILE__),
		'VERSION'  => '1.0.0',
	]);
}

class BCLPaymentLink {
	public function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	private function load_dependencies(): void {
		require_once BCL_PAYMENT_LINK['PATH'] . 'include/bcl-order-column.php';
		require_once BCL_PAYMENT_LINK['PATH'] . 'include/bcl-woocommerce-settings.php';
		require_once BCL_PAYMENT_LINK['PATH'] . 'include/bcl-cron.php';
	}

	private function init_hooks(): void {
		add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
		add_filter('plugin_action_links_' . BCL_PAYMENT_LINK['HOOK'], [$this, 'add_settings_link']);
		add_filter('plugin_row_meta', [$this, 'add_plugin_meta'], 10, 2);
	}

	public function load_plugin_textdomain(): void {
		load_plugin_textdomain('bcl-payment-link', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	public function enqueue_admin_assets(): void {
		wp_enqueue_style('bcl-styles', BCL_PAYMENT_LINK['URL'] . 'css/bcl-styles.css', [], BCL_PAYMENT_LINK['VERSION']);
		wp_enqueue_script('bcl-admin-script', BCL_PAYMENT_LINK['URL'] . 'js/bcl-admin.js', ['jquery'], BCL_PAYMENT_LINK['VERSION'], true);
		wp_localize_script('bcl-admin-script', 'bcl_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('bcl_nonce')
		]);
	}

	public function add_settings_link($actions): array {
		$settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=bcl_settings') . '">' . __('Settings', 'bcl-payment-link') . '</a>';
		array_unshift($actions, $settings_link);
		return $actions;
	}

	public function add_plugin_meta($links, $file): array {
		if ($file === BCL_PAYMENT_LINK['HOOK']) {
			$row_meta = [
				'docs' => '<a href="https://docs.bcl.my" target="_blank">' . __('Docs', 'bcl-payment-link') . '</a>',
				'register_account' => '<a href="https://bcl.my/register" target="_blank">' . __('Register Account', 'bcl-payment-link') . '</a>',
			];
			return array_merge($links, $row_meta);
		}
		return $links;
	}
}

new BCLPaymentLink();