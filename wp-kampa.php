<?php
/*
Plugin Name: WP Kampa!
Version: 0.2.4
Plugin URI: https://www.29lab.jp/wordpress-plugin
Description: WP Kampa! plugins makes it easy to post links to 'Kampa!'.
Author: Kiminori KATO
Author URI: https://www.29lab.jp/
License: GPL2
*/
/*
 Copyright (c) 2018 Kiminori Kato  (email : kimikato@29lab.jp)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('WPKAMPA_VERSION', '0.2.4');
define('WPKAMPA_PLUGIN_DIR', untrailingslashit(dirname(__FILE__)));
define('WPKAMPA_DOMAIN', 'wp-kampa');
define('WPKAMPA_KAMPA_API_LIST_URL', 'http://kampa.me/api/queue/');
define('WPKAMPA_KAMPA_API_DETAIL_URL', 'http://kampa.me/t/');

require_once WPKAMPA_PLUGIN_DIR . '/includes/class_wpkampa_list_table.php';


class WpKampa {

    public $options;

    /* コンストラクタ */
    public function __construct() {
        // プラグインオプションの読み込み
        $this->options = $this->load_plugin_options();

        // プラグインが有効化された時に実行されるメソッドを登録
        if (function_exists('register_activation_hook'))
            register_activation_hook(__FILE__, array(&$this, 'activation'));
        
        // プラグインが停止された時に実行されるメソッドを登録
        if (function_exists('register_deactivation_hook'))
            register_deactivation_hook(__FILE__, array(&$this, 'deactivation'));
        
        // アクションフックの設定
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'admin_menu'));

        // ショートコードの設定
        add_shortcode('kampa', array(&$this, 'shortcode'));
    }

    // load options
    public function load_plugin_options() {
        global $wpdb;

        $values = array();
        $results = $wpdb->get_results("
            SELECT *
              FROM $wpdb->options
             WHERE 1 = 1
               AND option_name like 'wpkampa_%'
             ORDER BY option_name
        ");

        foreach($results as $result) {
            $values[$result->option_name] = $result->option_value;
        }

        return $values;
    }

    // plugin activation
    public function activation() {
    }

    // plugin deactivation
    public function deactivation() {
    }

    // admin init
    public function admin_init() {
        if (is_admin()) 
            wp_enqueue_style('admin', plugin_dir_url(__FILE__).'css/admin.css');
    }

    private function getPluginDisplayName() {
        return 'Wp Kampa!';
    }

    private function getListPageSlug() {
        return get_class($this) . 'ListPage';
    }

    private function getSettingsSlug() {
        return get_class($this) . 'Settings';
    }

    // admin menu
    public function admin_menu() {
        $displayName = $this->getPluginDisplayName();
        $menuSlug = $this->getListPageSlug();
        $settingsSlug = $this->getSettingsSlug();

        if (function_exists('add_menu_page')) {
            add_menu_page(
                $displayName,
                'Wp Kampa!',
                'administrator',
                $menuSlug,
                array(&$this, 'wpkampa_admin_list_page')
            );
        }

        if (function_exists('add_submenu_page')) {
            add_submenu_page(
                $menuSlug,
                $displayName . ' Options',
                '設定',
                'manage_options',
                $settingsSlug,
                array(&$this, 'wpkampa_admin_opt_page')
            );
        }
    }

    // list page
    public function wpkampa_admin_list_page() {
        // Consumer keyの取得
        $consumer_key = $this->options["wpkampa_consumer_key"];

        // List Tableの設定
        $list_table = new WpKampa_List_Table();
        $list_table->prepare_items();
?>
<div class="wrap">
    <h2 class="wpkampa-admin-title">Wp Kampa!</h2>
    <?php $list_table->display(); ?>
</div>
<?php
    }

    // option page
    public function wpkampa_admin_opt_page() {
        // Consumer keyの取得
        $consumer_key = $this->options["wpkampa_consumer_key"];
?>
<div class="wrap">
    <h2 class="wpkampa-admin-title">Wp Kampa!</h2>
    <div>
        <form method="post" action="options.php">
            <?= wp_nonce_field('update-options'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Consumer key</th>
                    <td><input type="text" name="wpkampa_consumer_key" value="<?= $consumer_key; ?>" size="30" maxlength="25" /></td>
                </tr>
                <tr>
                    <td colspan="2">※ <a href="https://kampa.me/" target="_blank">Kampa!</a> サイトの[ 設定 ] - [ アカウント情報 ] の開発者向情報（API）に記載されている Consumer key を入力してください。</td>
                </tr>
            </table>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="wpkampa_consumer_key" />
            <?php submit_button(); ?>
        </form>
    </div>
</div>
<?php
    }

    // shortcode
    public function shortcode($atts) {
        $item_bs = $atts['item'];

        if ($this->is_exists_item_bs($item_bs)) {
            $url = 'https://kampa.me/t/parts/' . $item_bs . '.js';
            return '<script src="' . $url . '"></script>';
        } else {
            return 'aaaa';
        }
    }

    private function is_exists_item_bs($item_bs) {
        $is_exists = false;
        $key = $this->options['wpkampa_consumer_key'];

        // カンパ一覧取得
        $list_request_url = WPKAMPA_KAMPA_API_LIST_URL . $key . '.json';
        $json = $this->get_api_data($list_request_url);

        foreach ($json as $item) {
            // 一覧に item_bs が存在するか
            if ($item["kmpid"] == $item_bs) {
                $is_exists = true;
            }
        }

        return $is_exists;
    }

    public function get_data() {
        $results = [];
        $key = $this->options['wpkampa_consumer_key'];

        // カンパ一覧取得
        $list_url = WPKAMPA_KAMPA_API_LIST_URL . $key . '.json';
        $json = $this->get_api_data($list_url);

        foreach($json as $item) {
            $detail_url = WPKAMPA_KAMPA_API_DETAIL_URL . $item["kmpid"] . '.json';
            $kampa = $this->get_api_data($detail_url);
            $results[] = $kampa;
        }

        return $results;
    }

    private function get_api_data($url) {
        $json = [];
        if (function_exists('curl_init')) {
            $json = $this->get_json_curl($url);
        } else {
            $json = $this->get_json_file_get_contents($url);
        }
        return $json;
    }

    private function get_json_curl($url) {
        $data = null;

        $option = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, $option);

        $json = curl_exec($ch);
        $info = curl_getinfo($ch);
        $errNo = curl_errno($ch);

        if ($errNo !== CURLE_OK) {
            return [];
        }

        if ($info['http_code'] !== 200) {
            return [];
        }

        $data = json_decode($json, true);

        return $data;
    }

    private function get_json_file_get_contents($url) {
        $data = null;

        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true)
        ));

        $res = file_get_contents($url, false, $context);

        $is_success = strpos($http_response_header[0], '200');

        if ($is_success == false) {
            return [];
        } else {
            $data = json_decode($res, true);
        }

        return $data;
    }
}


$WpKampa = new WpKampa();

?>
