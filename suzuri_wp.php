<?php
/*
  Plugin Name: SUZURI for WP
  Plugin URI:
  Description: SUZURIの自分の商品を表示するサイドバー
  Version: 1.0.0
  Author: Yamap
  Author URI: https://github.com/skylarking85/suzuri_wp
  License: GPLv2
*/

require_once dirname( __FILE__ ) . '/lib/widget.php';

 add_action('init', 'SuzuriForWp::init');

 class SuzuriForWp {
    const VERSION           = '1.0.0';
    const PLUGIN_ID         = 'suzuri-for-wp';
    const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
    const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';
    const PLUGIN_DB_PREFIX  = self::PLUGIN_ID . '_';
    const CONFIG_MENU_SLUG  = self::PLUGIN_ID . '-config';
    const COMPLETE_CONFIG   = self::PLUGIN_ID . '-complete';

    static function init() {
        return new self();
    }

    function __construct() {
        if (is_admin() && is_user_logged_in()) {
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            add_action('admin_init', [$this, 'save_config']);
            add_action('admin_head', [$this, 'custom_admin_style']);
        }

        $urlpath = plugins_url('style.css', __FILE__);
        wp_register_style(self::PLUGIN_ID . '_style', $urlpath);
        wp_enqueue_style(self::PLUGIN_ID . '_style');
    }

    function set_plugin_menu() {
        add_menu_page(
            'SUZURI for WP',
            'SUZURI for WP',
            'manage_options',
            self::PLUGIN_ID,
            [$this, 'show_config_form'],
            'dashicons-awards',
            80
        );
    }

    function show_config_form() {
        $api_key = get_option(self::PLUGIN_DB_PREFIX . "_api_key");
        $user_name = get_option(self::PLUGIN_DB_PREFIX . "_user_name");
        $limit = get_option(self::PLUGIN_DB_PREFIX . "_limit");
        $product_type = get_option(self::PLUGIN_DB_PREFIX . "_product_type");
        $choice_id = get_option(self::PLUGIN_DB_PREFIX . "_choice_id");
        $is_cache = get_option(self::PLUGIN_DB_PREFIX . "_is_cache");
    ?>
    <div class="wrap suzuri_for_wp__setting">
        <h1>SUZURI for WP</h1>
        <p>SUZURIの自分の商品を表示するプラグインです</p>
        <h2>SUZURI API利用設定</h2>
        <p>API keyは<a href="https://suzuri.jp/developer/" target="_blank">SUZURI developer</a>ページから確認してください</p>
        <form action="" method='post' id="my-submenu-form">
        <?php wp_nonce_field(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ?>

        <p>
            <label for="title" class="sfw_label">API KEY：</label>
            <input type="text" name="api_key" value="<?= $api_key ?>"/>
        </p>
        <p>
            <label for="user_name" class="sfw_label">USER NAME：</label>
            <input type="text" name="user_name" value="<?= $user_name ?>"/>
        </p>
        <hr>
        <p>
            <label for="limit" class="sfw_label">表示件数：</label>
            <select name="limit">
                <?php for($i=1; $i <= 20; $i++): ?>
                <option value="<?php echo $i; ?>"<?php if($i == $limit): echo" selected='selected'"; endif; ?>>
                    <?php echo $i; ?>
                </option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <label for="product_type" class="sfw_label">表示したい商品：</label>
            <input type="radio" name="product_type" value="newer" id="product_type_1"<?php if($product_type == 'newer'): echo" checked='checked'"; endif; ?>>
                <label for="product_type_1">全商品から最新順</label>
            <input type="radio" name="product_type" value="choice" id="product_type_2"<?php if($product_type == 'choice'): echo" checked='checked'"; endif; ?>>
                <label for="product_type_2">オモイデ</label>
        </p>
        <p>表示したい商品がオモイデの場合オモイデIDを指定してください。</p>
        <p>
            <label for="choice" class="sfw_label">オモイデID：</label>
            <input type="text" name="choice_id" value="<?= $choice_id; ?>"/>
        </p>

        <p><label for="is-cache" class="sfw_label">表示にキャッシュを使う(推奨)</label>
            <input type="checkbox" name="is_cache" value="1" id="is-cache"<?php if($is_cache): echo" checked='checked'"; endif; ?>><br>
            <div class="sfw_description">チェックを入れた後初のウィジェット表示でAPIから取得したデータをWPに保存し、以降表示に使います。<br>商品の入れ替えなどでキャッシュを削除したい際は、一度「キャッシュの削除」からデータを削除し、再度ウィジェットを表示させてデータを取得してください。<br>
            <input type="submit" class="button button-secondary" name="clear_cache" value="キャッシュの削除"></div>
        </p>

        <p><input type='submit' value='保存' name="config_save" class='button button-primary button-large'></p>
        </form>
    </div>
    <?php
        $complete = get_transient(self::COMPLETE_CONFIG);
        echo $complete;
    }

    function save_config() {
        if (isset($_POST[self::CREDENTIAL_NAME]) && $_POST[self::CREDENTIAL_NAME]) {
            if (check_admin_referer(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME)) {

                if($_POST['clear_cache']) {
                    update_option(self::PLUGIN_DB_PREFIX . "_product_data", '');
                    $msg = "SUZURI商品データのキャッシュを削除しました";
                }
                if($_POST['config_save']) {
                    $api_key = isset($_POST['api_key']) ? $_POST['api_key'] : get_option(self::PLUGIN_DB_PREFIX . "_api_key");
                    $user_name = isset($_POST['user_name']) ? $_POST['user_name'] : get_option(self::PLUGIN_DB_PREFIX . "_user_name");
                    $limit = isset($_POST['limit']) ? $_POST['limit'] : get_option(self::PLUGIN_DB_PREFIX . "_limit");
                    $product_type = isset($_POST['product_type']) ? $_POST['product_type'] : get_option(self::PLUGIN_DB_PREFIX . "_product_type");
                    $choice_id = isset($_POST['choice_id']) ? $_POST['choice_id'] : get_option(self::PLUGIN_DB_PREFIX . "_choice_id");
                    $is_cache = isset($_POST['is_cache']) ? $_POST['is_cache'] : get_option(self::PLUGIN_DB_PREFIX . "_cache_id");

                    update_option(self::PLUGIN_DB_PREFIX . "_api_key", $api_key);
                    update_option(self::PLUGIN_DB_PREFIX . "_user_name", $user_name);
                    update_option(self::PLUGIN_DB_PREFIX . "_limit", $limit);
                    update_option(self::PLUGIN_DB_PREFIX . "_product_type", $product_type);
                    update_option(self::PLUGIN_DB_PREFIX . "_choice_id", $choice_id);
                    update_option(self::PLUGIN_DB_PREFIX . "_is_cache", $is_cache);

                    $msg = "設定の保存が完了しました。";
                }

                set_transient(self::COMPLETE_CONFIG, $msg, 5);
                wp_safe_redirect(menu_page_url(self::CONFIG_MENU_SLUG));

            }
        }
    }

    function custom_admin_style() {
    ?>
    <style>
        .suzuri_for_wp__setting .sfw_label {
            display: inline-block;
            width: 200px;
            font-weight: 600;
        }
        .suzuri_for_wp__setting .sfw_description {
            margin-left: 200px;
        }
        .suzuri_for_wp__setting .sfw_description .button{
            margin-top: 10px;
        }
    </style>
    <?php }

 }