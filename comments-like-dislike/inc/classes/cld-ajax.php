<?php

if (!class_exists('CLD_Ajax')) {

    class CLD_Ajax extends CLD_Library {

        function __construct() {
            add_action('wp_ajax_cld_comment_ajax_action', array($this, 'like_dislike_action'));
            add_action('wp_ajax_nopriv_cld_comment_ajax_action', array($this, 'like_dislike_action'));

            add_action('wp_ajax_cld_comment_undo_ajax_action', array($this, 'like_dislike_undo_action'));
            add_action('wp_ajax_nopriv_cld_comment_undo_ajax_action', array($this, 'like_dislike_undo_action'));
        }

        function like_dislike_action() {
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'cld-ajax-nonce')) {
                $comment_id = sanitize_text_field($_POST['comment_id']);
                /**
                 * Action cld_before_ajax_process
                 * Fires just before the ajax process
                 *
                 * @param type int $comment_id
                 *
                 * @since 1.0.7
                 */
                do_action('cld_before_ajax_process', $comment_id);



                $type = sanitize_text_field($_POST['type']);

                $cld_settings = get_option('cld_settings');
                /**
                 * Cookie Validation
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'cookie' && isset($_COOKIE['cld_' . $comment_id])) {
                    $response_array = array('success' => false, 'message' => 'Invalid action');
                    echo json_encode($response_array);
                    die();
                }
                /**
                 * IP Validation
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'ip') {
                    $user_ip = $this->get_user_IP();

                    $liked_ips = get_comment_meta($comment_id, 'cld_ips', true);

                    if (empty($liked_ips)) {
                        $liked_ips = array();
                    }
                    if ((in_array($user_ip, $liked_ips))) {
                        $response_array = array('success' => false, 'message' => 'Invalid action');
                        echo json_encode($response_array);
                        die();
                    }
                }
                /**
                 * User Logged in validation
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'user') {
                    if (is_user_logged_in()) {
                        $liked_users = get_comment_meta($comment_id, 'cld_users', true);
                        $liked_users = (empty($liked_users)) ? array() : $liked_users;
                        $current_user_id = get_current_user_id();
                        if (in_array($current_user_id, $liked_users)) {
                            $response_array = array('success' => false, 'message' => 'Invalid action');
                            echo json_encode($response_array);
                            die();
                        }
                    } else {
                        $response_array = array('success' => false, 'message' => 'Invalid action');
                        echo json_encode($response_array);
                        die();
                    }
                }
                if ($type == 'like') {
                    $like_count = get_comment_meta($comment_id, 'cld_like_count', true);
                    if (empty($like_count)) {
                        $like_count = 0;
                    }
                    $like_count = $like_count + 1;
                    $check = update_comment_meta($comment_id, 'cld_like_count', $like_count);

                    if ($check) {

                        $response_array = array('success' => true, 'latest_count' => $like_count);
                    } else {
                        $response_array = array('success' => false, 'latest_count' => $like_count);
                    }
                } else {
                    $dislike_count = get_comment_meta($comment_id, 'cld_dislike_count', true);
                    if (empty($dislike_count)) {
                        $dislike_count = 0;
                    }
                    $dislike_count = $dislike_count + 1;
                    $check = update_comment_meta($comment_id, 'cld_dislike_count', $dislike_count);
                    if ($check) {
                        $response_array = array('success' => true, 'latest_count' => $dislike_count);
                    } else {
                        $response_array = array('success' => false, 'latest_count' => $dislike_count);
                    }
                }

                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'cookie') {
                    $cookie_name = 'cld_' . $comment_id;
                    setcookie($cookie_name, $type, time() + 3600 * 24 * 365, '/');
                }
                /**
                 * Check the liked ips and insert the user ips for future checking
                 *
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'ip') {
                    $liked_ips = get_comment_meta($comment_id, 'cld_ips', true);
                    $liked_ips = (empty($liked_ips)) ? array() : $liked_ips;

                    $liked_ips_info = get_comment_meta($comment_id, 'cld_ips_info', true);
                    $liked_ips_info = (empty($liked_ips_info)) ? array() : $liked_ips_info;
                    if (!in_array($user_ip, $liked_ips)) {
                        $liked_ips[] = $user_ip;
                        $liked_ips_info[md5($user_ip)] = $type;
                    }

                    update_comment_meta($comment_id, 'cld_ips', $liked_ips);
                    update_comment_meta($comment_id, 'cld_ips_info', $liked_ips_info);
                }
                /**
                 * Check if user is logged in to check user login for like dislike action
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'user') {
                    if (is_user_logged_in()) {

                        $liked_users = get_comment_meta($comment_id, 'cld_users', true);
                        $liked_users = (empty($liked_users)) ? array() : $liked_users;

                        $liked_users_info = get_comment_meta($comment_id, 'cld_users_info', true);
                        $liked_users_info = (empty($liked_users_info)) ? array() : $liked_users_info;

                        $current_user_id = get_current_user_id();
                        if (!in_array($current_user_id, $liked_users)) {
                            $liked_users[] = $current_user_id;
                        }
                        $liked_users_info[$current_user_id] = $type;
                        update_comment_meta($comment_id, 'cld_users', $liked_users);
                        update_comment_meta($comment_id, 'cld_users_info', $liked_users_info);
                    }
                }
                /**
                 * Action cld_after_ajax_process
                 *
                 * @param type int $comment_id
                 *
                 * @since 1.0.7
                 */
                do_action('cld_after_ajax_process', $comment_id);
                echo json_encode($response_array);

                //$this->print_array( $response_array );
                die();
            } else {
                die('No script kiddies please!');
            }
        }
        function like_dislike_undo_action() {
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'cld-ajax-nonce')) {
                $comment_id = sanitize_text_field($_POST['comment_id']);
                /**
                 * Action cld_before_undo_ajax_process
                 * Fires just before the undo ajax process
                 *
                 * @param type int $comment_id
                 *
                 * @since 1.1.9
                 */
                do_action('cld_before_undo_ajax_process', $comment_id);



                $type = sanitize_text_field($_POST['type']);

                $cld_settings = get_option('cld_settings');

                /**
                 * Cookie Validation
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'cookie' && !isset($_COOKIE['cld_' . $comment_id])) {
                    $response_array = array('success' => false, 'message' => 'Invalid action');
                    echo json_encode($response_array);
                    die();
                }
                /**
                 * IP Validation
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'ip') {
                    $user_ip = $this->get_user_IP();

                    $liked_ips = get_comment_meta($comment_id, 'cld_ips', true);

                    if (empty($liked_ips)) {
                        $liked_ips = array();
                    }
                    if ((!in_array($user_ip, $liked_ips))) {
                        $response_array = array('success' => false, 'message' => 'Invalid action');
                        echo json_encode($response_array);
                        die();
                    }
                }
                /**
                 * User Logged in validation
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'user') {
                    if (is_user_logged_in()) {
                        $liked_users = get_comment_meta($comment_id, 'cld_users', true);
                        $liked_users = (empty($liked_users)) ? array() : $liked_users;
                        $current_user_id = get_current_user_id();
                        if (!in_array($current_user_id, $liked_users)) {
                            $response_array = array('success' => false, 'message' => 'Invalid action');
                            echo json_encode($response_array);
                            die();
                        }
                    } else {
                        $response_array = array('success' => false, 'message' => 'Invalid action');
                        echo json_encode($response_array);
                        die();
                    }
                }
                if ($type == 'like') {
                    $like_count = get_comment_meta($comment_id, 'cld_like_count', true);
                    if (empty($like_count)) {
                        $like_count = 0;
                    }
                    $like_count = $like_count - 1;
                    $check = update_comment_meta($comment_id, 'cld_like_count', $like_count);

                    if ($check) {

                        $response_array = array('success' => true, 'latest_count' => $like_count);
                    } else {
                        $response_array = array('success' => false, 'latest_count' => $like_count);
                    }
                } else {
                    $dislike_count = get_comment_meta($comment_id, 'cld_dislike_count', true);
                    if (empty($dislike_count)) {
                        $dislike_count = 0;
                    }
                    $dislike_count = $dislike_count - 1;
                    $check = update_comment_meta($comment_id, 'cld_dislike_count', $dislike_count);
                    if ($check) {
                        $response_array = array('success' => true, 'latest_count' => $dislike_count);
                    } else {
                        $response_array = array('success' => false, 'latest_count' => $dislike_count);
                    }
                }

                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'cookie') {
                    $cookie_name = 'cld_' . $comment_id;
                    setcookie($cookie_name, $type, time() - 3600 * 24 * 365, '/');
                }
                /**
                 * Check the liked ips and insert the user ips for future checking
                 *
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'ip') {
                    $liked_ips = get_comment_meta($comment_id, 'cld_ips', true);
                    $liked_ips = (empty($liked_ips)) ? array() : $liked_ips;

                    $liked_ips_info = get_comment_meta($comment_id, 'cld_ips_info', true);
                    $liked_ips_info = (empty($liked_ips_info)) ? array() : $liked_ips_info;
                    if (in_array($user_ip, $liked_ips)) {
                        $liked_ips = array_diff($liked_ips, [$user_ip]);
                        unset($liked_ips_info[md5($user_ip)]);
                    }

                    update_comment_meta($comment_id, 'cld_ips', $liked_ips);
                    update_comment_meta($comment_id, 'cld_ips_info', $liked_ips_info);
                }
                /**
                 * Check if user is logged in to check user login for like dislike action
                 */
                if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'user') {
                    if (is_user_logged_in()) {

                        $liked_users = get_comment_meta($comment_id, 'cld_users', true);
                        $liked_users = (empty($liked_users)) ? array() : $liked_users;

                        $liked_users_info = get_comment_meta($comment_id, 'cld_users_info', true);
                        $liked_users_info = (empty($liked_users_info)) ? array() : $liked_users_info;

                        $current_user_id = get_current_user_id();
                        if (in_array($current_user_id, $liked_users)) {
                            $liked_users = array_diff($liked_users, [$current_user_id]);
                            unset($liked_users_info[$current_user_id]);
                        }

                        update_comment_meta($comment_id, 'cld_users', $liked_users);
                        update_comment_meta($comment_id, 'cld_users_info', $liked_users_info);
                    }
                }
                /**
                 * Action cld_after_undo_ajax_process
                 * Fires just after the undo ajax process
                 *
                 * @param type int $comment_id
                 *
                 * @since 1.1.9
                 */
                do_action('cld_after_undo_ajax_process', $comment_id);
                echo json_encode($response_array);

                //$this->print_array( $response_array );
                die();
            } else {
                die('No script kiddies please!');
            }
        }
    }

    new CLD_Ajax();
}
