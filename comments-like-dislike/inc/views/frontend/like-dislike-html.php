<?php
$cld_settings = get_option('cld_settings');
if (!empty($custom_function)) {
    $cld_settings['basic_settings']['status'] = 1;
}
if ($cld_settings['basic_settings']['status'] != 1) {
    // if comments like dislike is disabled from backend
    return;
}
if (isset($comment)) {
    $comment_id = $comment->comment_ID;
}
if (empty($comment_id)) {
    return;
}


$already_liked = 0;
$already_liked_type = 'na';
$href = 'javascript:void(0)';

/**
 * Cookie Validation
 */
if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'cookie' && isset($_COOKIE['cld_' . $comment_id])) {
    $already_liked = 1;
    $already_liked_type = ($_COOKIE['cld_' . $comment_id] != 1) ? $_COOKIE['cld_' . $comment_id] : 'na';
}
/**
 * IP Validation
 */
if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'ip') {
    $user_ip = $this->get_user_IP();
    $liked_ips = get_comment_meta($comment_id, 'cld_ips', true);
    $liked_ips_info = get_comment_meta($comment_id, 'cld_ips_info', true);
    if (empty($liked_ips)) {
        $liked_ips = array();
    }
    if (empty($liked_ips_info)) {
        $liked_ips_info = array();
    }
    if ((in_array($user_ip, $liked_ips))) {
        $already_liked = 1;
        if (isset($liked_ips_info[md5($user_ip)])) {
            $already_liked_type = $liked_ips_info[md5($user_ip)];
        } else {
            $already_liked_type = 'na';
        }
    }
}

/**
 * User Logged in validation
 */
if ($cld_settings['basic_settings']['like_dislike_resistriction'] == 'user') {
    if (is_user_logged_in()) {
        $liked_users = get_comment_meta($comment_id, 'cld_users', true);
        $liked_users = (empty($liked_users)) ? array() : $liked_users;

        $liked_users_info = get_comment_meta($comment_id, 'cld_users_info', true);
        $liked_users_info = (empty($liked_users_info)) ? array() : $liked_users_info;

        $current_user_id = get_current_user_id();
        if (in_array($current_user_id, $liked_users)) {
            $already_liked = 1;
            if (isset($liked_users_info[$current_user_id])) {
                $already_liked_type = $liked_users_info[$current_user_id];
            } else {
                $already_liked_type = 'na';
            }
        }
        $href = 'javascript:void(0)';
    } else {
        if (!empty($cld_settings['basic_settings']['login_link'])) {
            $href = $cld_settings['basic_settings']['login_link'];
        } else {
            $href = 'javascript:void(0)';
        }
    }
}

$like_title = isset($cld_settings['basic_settings']['like_hover_text']) ? esc_attr($cld_settings['basic_settings']['like_hover_text']) : __('Like', CLD_TD);
$dislike_title = isset($cld_settings['basic_settings']['dislike_hover_text']) ? esc_attr($cld_settings['basic_settings']['dislike_hover_text']) : __('Dislike', CLD_TD);
$like_count = get_comment_meta($comment_id, 'cld_like_count', true);
$dislike_count = get_comment_meta($comment_id, 'cld_dislike_count', true);

if (!empty($cld_settings['basic_settings']['display_zero'])) {
    $like_count = (empty($like_count)) ? 0 : $like_count;
    $dislike_count = (empty($dislike_count)) ? 0 : $dislike_count;
}
/**
 * Filters like count
 *
 * @param type int $like_count
 * @param type int $comment_id
 *
 * @since 1.0.0
 */
$like_count = apply_filters('cld_like_count', $like_count, $comment_id);

/**
 * Filters dislike count
 *
 * @param type int $dislike_count
 * @param type int $comment_id
 *
 * @since 1.0.0
 */
$dislike_count = apply_filters('cld_dislike_count', $dislike_count, $comment_id);
?>
<div class="cld-like-dislike-wrap cld-<?php echo esc_attr($cld_settings['design_settings']['template']); ?>">
    <?php
    /**
     * Like Dislike Order
     */
    if ($cld_settings['basic_settings']['display_order'] == 'like-dislike') {
        if ($cld_settings['basic_settings']['like_dislike_display'] != 'dislike_only') {
            include(CLD_PATH . 'inc/views/frontend/like.php');
        }
        if ($cld_settings['basic_settings']['like_dislike_display'] != 'like_only') {
            include(CLD_PATH . 'inc/views/frontend/dislike.php');
        }
    } else {
        /**
         * Dislike Like Order
         */
        if ($cld_settings['basic_settings']['like_dislike_display'] != 'like_only') {
            include(CLD_PATH . 'inc/views/frontend/dislike.php');
        }
        if ($cld_settings['basic_settings']['like_dislike_display'] != 'dislike_only') {
            include(CLD_PATH . 'inc/views/frontend/like.php');
        }
    }
    ?>
</div>