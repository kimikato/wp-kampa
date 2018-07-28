<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WpKampa_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'item',
            'plural'   => 'items',
            'ajax'     => false
        ) );
    }

    // カラムのデフォルト設定
    function column_default( $item, $column_name ) {
        return '';
    }

    // 列の識別子の設定
    function get_columns() {
        return $columns = array(
            'short_code' => 'ショートコード',
            'example'    => '表示例'
        );
    }

    // ソート列の設定
    function get_sortable_columns() {
        $sortable_columns = array();
        return $sortable_columns;
    }

    // 一括操作の設定
    function get_bulk_actions() {
        $actions = array();
        return $actions;
    }

    // 一括操作の適用ボタン押下時の処理
    function process_bulk_action() {
        // none
    }

    // テーブル情報の設定
    function prepare_items() {

        $current_screen = get_current_screen();

        // 1ページに表示する件数
        $per_page = 5;

        // 列の設定
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        // 列ヘッダーの設定
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // 一括操作の設定
        $this->process_bulk_action();

        // データの取得
        $wpkampa = new WpKampa();
        $data = $wpkampa->get_data();
        
        // 現在のページ番号
        $current_page = $this->get_pagenum();

        // アイテム総数
        $total_items = count($data);

        $this->items = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );
    }

    function single_row($item) {
        list($columns, $hidden, $sortable, $primary) = $this->get_column_info();
?>
<tr>
<?php
        foreach($columns as $column_name => $column_display_name) {
            $classes = "$column_name column-$column_name";
            $extra_classes = '';
            if ( in_array( $column_name, $hidden ) ) {
                $extra_classes = ' hidden';
            }

            switch($column_name) {
                case 'short_code':
                    $short_code = '[kampa item="' . $item["item_bs"] . '"]';
?>
<td class="<?php echo esc_attr( $classes.$extra_classes ); ?>">
    <div class="wpkampa_admin_list_short_code">
        <input type="text" size="20" value="<?= htmlspecialchars($short_code); ?>" onfocus="this.select();" readonly />
    </div>
</td>
<?php
                    break;
                case 'example':
                    $example_url = "https://kampa.me/t/parts/" . $item["item_bs"] . ".js";
?>
<td class="<?php echo esc_attr( $classes.$extra_classes ); ?>">
    <div class="wpkampa_admin_list_example">
        <script src="<?= $example_url ?>"></script>
    </div>
</td>
<?php
                    break;
            }
        }
?>
</tr>
<?php
    }
}

?>
