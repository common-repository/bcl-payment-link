<?php
namespace BCLPaymentLink;

defined('ABSPATH') || exit;

add_filter('woocommerce_settings_tabs_array', __NAMESPACE__ . '\bcl_add_settings_tab', 50);
add_action('woocommerce_settings_tabs_bcl_settings', __NAMESPACE__ . '\bcl_settings_tab');
add_action('woocommerce_update_options_bcl_settings', __NAMESPACE__ . '\bcl_update_settings');

function bcl_add_settings_tab($settings_tabs) {
	$settings_tabs['bcl_settings'] = __('BCL Payment Link', 'bcl-payment-link');
	return $settings_tabs;
}

function bcl_settings_tab(): void {
	woocommerce_admin_fields(bcl_get_settings());
}

function bcl_update_settings(): void {
	woocommerce_update_options(bcl_get_settings());
}

function bcl_get_settings() {
	$settings = [
		'section_title' => [
			'name'  => __('BCL Payment Link Settings', 'bcl-payment-link'),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'bcl_section_title',
		],
		'api_token' => [
			'name'     => __('API Token', 'bcl-payment-link'),
			'type'     => 'text',
			'desc'     => __('Team Owner/leader can get this token on token Setting.', 'bcl-payment-link'),
			'id'       => 'bcl_api_token',
			'custom_attributes' => [
				'required' => 'required'
			],
			'default'  => '',
		],
		'portal_key' => [
			'name'     => __('Portal Key', 'bcl-payment-link'),
			'type'     => 'text',
			'desc'     => __('Portal Key from BCL console to generate payment link.', 'bcl-payment-link'),
			'id'       => 'bcl_portal_key',
			'custom_attributes' => [
				'required' => 'required'
			],
			'default'  => '',
		],
		'order_prefix' => [
			'name'     => __('Order Prefix', 'bcl-payment-link'),
			'type'     => 'text',
			'desc'     => __('Prefix for payment link orders. Example: SITEA-116', 'bcl-payment-link'),
			'id'       => 'bcl_order_prefix',
			'custom_attributes' => [
				'required' => 'required'
			],
			'default'  => '',
		],
		'payment_select' => [
			'name'     => __('Payment Method', 'bcl-payment-link'),
			'type'     => 'select',
			'desc'     => __('Select the payment method', 'bcl-payment-link'),
			'id'       => 'bcl_payment_select',
			'options'  => [
				'1' => __('FPX (Current & Saving Account)', 'bcl-payment-link'),
				'4' => __('Line of Credit', 'bcl-payment-link'),
				'5' => __('DuitNow OBW', 'bcl-payment-link'),
			],
			'default'  => '1',
		],
		'section_end' => [
			'type' => 'sectionend',
			'id'   => 'bcl_section_end'
		]
	];

	return apply_filters('bcl_payment_link_settings', $settings);
}