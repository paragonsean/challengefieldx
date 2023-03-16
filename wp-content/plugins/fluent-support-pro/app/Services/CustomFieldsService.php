<?php

namespace FluentSupportPro\App\Services;

use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;

class CustomFieldsService
{
    private static $optionKey = '_ticket_custom_fields';

    public static function getCustomFields()
    {
        return Helper::getOption(self::$optionKey, []);
    }

    public static function updateCustomFields($fields)
    {
        return Helper::updateOption(self::$optionKey, $fields);
    }

    public static function getFieldTypes()
    {
        return apply_filters('fluent_support/custom_field_types', [
            'text'          => [
                'type'       => 'text',
                'label'      => 'Single Line Text',
                'value_type' => 'string'
            ],
            'textarea'      => [
                'type'       => 'textarea',
                'label'      => 'Multi Line Text',
                'value_type' => 'string'
            ],
            'number'        => [
                'type'       => 'number',
                'label'      => 'Numeric Field',
                'value_type' => 'numeric'
            ],
            'select-one' => [
                'type'       => 'select-one',
                'label'      => 'Select choice',
                'value_type' => 'string'
            ],
            'radio'         => [
                'type'       => 'radio',
                'label'      => 'Radio Choice',
                'value_type' => 'string'
            ],
            'checkbox'      => [
                'type'       => 'checkbox',
                'label'      => 'Checkboxes',
                'value_type' => 'array'
            ]
        ]);
    }

    public static function getFieldSlugs()
    {
        $fields = self::getCustomFields();

        $slugs = [];
        foreach ($fields as $field) {
            $slugs[] = $field['slug'];
        }

        return $slugs;
    }

    public static function getFieldLabels($scope = 'public')
    {
        $fields = self::getCustomFields();

        if (!$fields) {
            return [];
        }

        $formattedData = [];

        foreach ($fields as $field) {

            if ($scope == 'public' && Arr::get($field, 'admin_only') == 'yes') {
                continue;
            }

            $label = $field['label'];
            if ($scope == 'admin' && !empty($label['admin_label'])) {
                $label = $label['admin_label'];
            }

            $field['label'] = $label;
            unset($field['admin_label']);

            $formattedData[$field['slug']] = $field;
        }

        return $formattedData;
    }


    public static function getRenderedPublicFields($customer = false)
    {
        if (!$customer) {
            $customer = Helper::getCurrentCustomer();
        }

        if (!$customer) {
            return [];
        }

        $publicFields = self::getFieldLabels();
        $fieldTypes = self::getFieldTypes();

        $validFields = [];

        foreach ($publicFields as $fieldIndex => $publicField) {
            $fieldType = Arr::get($fieldTypes, $publicField['type']);
            if (!$fieldType) {
                continue;
            }

            if (!empty($fieldType['is_remote'])) {
                $publicField = apply_filters('fluent_support/render_custom_field_options_' . $fieldType['type'], $publicField, $customer);
                if (!$publicField || empty($publicField['rendered'])) {
                    continue;
                }
            }

            $validFields[$fieldIndex] = $publicField;
        }

        return $validFields;

    }

    public static function getCustomerRenderers()
    {
		$fieldTypes = [
			'woo_orders',
			'woo_products',
			'edd_orders',
			'edd_products',
			'learndash_courses',
			'learndash_user_courses',
			'llms_user_courses',
			'llms_courses',
			'pmpro_levels',
			'pmpro_user_levels',
			'rcpro_levels',
			'rcpro_user_levels',
			'tutorlms_courses',
			'tutorlms_user_courses',
			'wlm_levels',
			'wlm_user_levels',
			'bb_groups',
			'bb_user_groups',
            'learnpress_courses',
            'learnpress_user_courses'
		];

        return apply_filters('fluent_support/custom_field_renders_type', $fieldTypes);
    }
}
