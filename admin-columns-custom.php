<?php
/*
Plugin Name: Admin Columns Custom
Version: 1.0.0
Description: Customize columns on the administration screens for post(types), users and other content. Filter and sort content, and edit posts directly from the posts overview. All via an intuitive, easy-to-use drag-and-drop interface.
Author: IZWEB
Author URI: http://izweb.biz
Plugin URI: http://izweb.biz
Domain Path: /languages/
Network: true
*/

class Admin_Columns_Custom{
    public $fields;

    function __construct(){
        add_filter( 'cac/editable/options', array($this, 'editable_column_settings'), 10, 2 );
        add_action( 'init', array($this, 'get_fields') );
    }

    /**
     * Get all fields from billing and shipping
     */
    function get_fields(){
        $billing_fields = apply_filters("woocommerce_billing_fields", array());
        $shipping_fields = apply_filters("woocommerce_shipping_fields", array());
        $this->fields = $billing_fields + $shipping_fields;
    }

    /**
     * Modify column editable type
     * @param $editable
     * @param $column
     * @return mixed
     */
    function editable_column_settings( $editable, $column ) {
        $key = str_replace("cpachidden_", "", $column['field'] );
        $screen = get_current_screen();

        if ( 'column-meta' == $column['type']) {
            if (array_key_exists($key, $this->fields) && $this->fields[$key]['type']== 'select' ) {
                $editable['type'] = 'select';
                $editable['options'] = $this->get_column_options($this->fields[$key]['options']);
            }

            if (!empty($screen) && $screen->id == 'users' && $column['field_type'] == 'array') {
                global $wpdb;
                $editable['type'] = 'select';
                $values = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->usermeta} WHERE meta_key='".$column['field']."'");
                $i=0;
                foreach ($values as $value) {
                    if (empty($value)) continue;
                    $editable['options'][$i]['value'] = $value;
                    $editable['options'][$i]['label'] = $value;
                    $i++;
                }
                $editable['options'][$i]['value'] = "";
                $editable['options'][$i]['label'] = "";
            }
        }

        return $editable;
    }

    /**
     * Reformat options
     * @param $options
     * @return mixed
     */
    function get_column_options($options){
        $i=0;
        foreach ($options as $key=>$value) {
            $select_options[$i]['value'] = $value;
            $select_options[$i]['label'] = $key;
            $i++;
        }
        return $select_options;
    }
}

new Admin_Columns_Custom();