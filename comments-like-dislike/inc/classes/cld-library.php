<?php

if (!class_exists('CLD_Library')) {

    class CLD_Library {

        var $cld_settings;

        function __construct() {
            $this->cld_settings = get_option('cld_settings');
        }

        function print_array($array) {
            echo "<pre>";
            print_r($array);
            echo "</pre>";
        }

        /**
         * Returns default settings array
         *
         * @return array
         *
         * @since 1.0.0
         */
        function get_default_settings() {
            $default_settings = array();
            $default_settings['basic_settings']['status'] = 0;
            $default_settings['basic_settings']['like_dislike_position'] = 'after';
            $default_settings['basic_settings']['like_dislike_display'] = 'both';
            $default_settings['basic_settings']['like_dislike_resistriction'] = 'cookie';
            $default_settings['basic_settings']['display_order'] = 'like-dislike';
            $default_settings['basic_settings']['like_hover_text'] = '';
            $default_settings['basic_settings']['dislike_hover_text'] = '';
            $default_settings['design_settings']['template'] = 'template-1';
            $default_settings['design_settings']['like_icon'] = '';
            $default_settings['design_settings']['dislike_icon'] = '';
            $default_settings['design_settings']['icon_color'] = '';
            $default_settings['design_settings']['count_color'] = '';
            /**
             * Filters deault settings
             *
             * @param type array $default_settings
             *
             * @since 1.0.0
             */
            return apply_filters('cld_default_settings', $default_settings);
        }

        /**
         * Returns visitors IP address
         *
         * @return string $ip
         *
         * @since 1.0.0
         */
        function get_user_IP() {

            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);

            // If the IP is in an unexpected format, fallback to a default
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                $ip = '127.0.0.1';
            }


            return $ip;
        }

        /**
         * Prints display none
         *
         * @param string $param1
         * @param string $param2
         *
         * @since 1.0.8
         */
        function display_none($param1, $param2) {
            if ($param1 != $param2) {
                echo 'style="display:none"';
            }
        }
    }

    $GLOBALS['cld_library'] = new CLD_Library();
}
