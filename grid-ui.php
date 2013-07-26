<?php
/**
Plugin Name: Grid UI
Plugin URI: http://dreimorgen.com
Description: A plugin to interface with different CSS grid systems in the edit window.
Version: 0.1
Author: Dreimorgen
Author URI: http://dreimorgen.com
Text Domain: grid-ui
*/

if (!class_exists('grid_ui')) {

  class grid_ui {

    /**
     * Instantiate the Plugin
     */
    function __construct() {
      if ( is_admin() ){

        load_plugin_textdomain('grid-ui', false, dirname(plugin_basename(__FILE__)) . '/lang/');

        // settings
        add_action( 'admin_menu', array( $this, 'add_settings_menu_item' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // edit forms
        add_action( "admin_init", array( $this, "load_assets" ) );
        add_action( "edit_form_after_title", array( $this, "add_ui" ) );

        $this->set_default_options();

        add_filter( 'mce_css', array( $this, 'grid_css_for_mce' ) );

      }
    }

    function grid_css_for_mce($wp) {
      $wp .= ',' . plugins_url('grid-ui/tinymce.css.php');
      return $wp;
    }

    /**
     * Set the default options on installation
     */
    function set_default_options() {
      if (get_option('gridui_columns') === FALSE) {
        add_option( 'gridui_columns', __( '12', 'grid-ui' ) );
      }
      if (get_option('gridui_prefix') === FALSE) {
        add_option( 'gridui_prefix', __( 'span', 'grid-ui' ) );
      }
      if (get_option('gridui_row') === FALSE) {
        add_option( 'gridui_row', __( 'row', 'grid-ui' ) );
      }
    }


    /**
     * Add the admin menu entry for the page and call create_settings_page()
     */
    function add_settings_menu_item() {
      //create new top-level menu
      add_menu_page(
        __( 'Grid UI Settings', 'grid-ui' ),
        __( 'Grid UI', 'grid-ui' ),
        'administrator',
        __FILE__,
        array( $this, 'create_settings_page' )
        );
    }


    /**
     * Register the section and settins
     */
    public function register_settings() {
      register_setting('grid_ui_options', 'gridui', array($this, 'check_settings'));

      add_settings_section(
        'grid-ui-settings-basic',
        __('<br />Here you can set the classes which fit for your grid system<br />', 'grid-ui'),
        false,
        'grid-ui-settings'
        );

      add_settings_field(
        'number_of_columns',
        __( 'Number of columns', 'grid-ui' ),
        array( $this, 'option_columns'),
        'grid-ui-settings',
        'grid-ui-settings-basic'
        );

      add_settings_field(
        'column_class_prefix',
        __( 'Column class prefix', 'grid-ui' ),
        array( $this, 'option_column_class_prefix'),
        'grid-ui-settings',
        'grid-ui-settings-basic'
        );

      add_settings_field(
        'row_class',
        __( 'Row class', 'grid-ui' ),
        array( $this, 'option_row_class'),
        'grid-ui-settings',
        'grid-ui-settings-basic'
        );


    }


    /**
     * Check the input from the options page
     */
    public function check_settings($input){
      $columns = $input['number_of_columns'];
      $prefix = $input['column_class_prefix'];
      $row = $input['row_class'];

      if (isset($columns)) {
        if (is_numeric($columns)) {
          if (get_option('gridui_columns') === FALSE) {
            add_option('gridui_columns', (int) $columns);
          } else {
            update_option('gridui_columns', (int) $columns);
          }
        } else {
          return new WP_Error('Broke', __('The amount of Columns has to be a numerical value!', 'grid-ui' ) );
        }
      }

      if (isset($prefix)) {
        if (get_option('gridui_prefix') === FALSE) {
          add_option('gridui_prefix', $prefix);
        } else {
          update_option('gridui_prefix', $prefix);
        }
      }

      if (isset($row)) {
        if (get_option('gridui_row') === FALSE) {
          add_option('gridui_row', $row);
        } else {
          update_option('gridui_row', $row);
        }
      }
    }


    /**
     * Add the Options page
     */
    public function create_settings_page() { ?>

    <div class="wrap">
      <?php screen_icon('options-general'); ?>
      <h2>Settings</h2>
      <form method="post" action="options.php">
        <?php
                      // This prints out all hidden setting fields
        settings_fields('grid_ui_options');
        do_settings_sections('grid-ui-settings');
        ?>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php }


    /**
     * Add the column number option
     */
    function option_columns() { ?>
    <input type="number" id="number_of_columns" name="gridui[number_of_columns]" value="<?=get_option('gridui_columns');?>" />
    <?php }


    /**
     * Add the column prefix option
     */
    function option_column_class_prefix() { ?>
    <input type="text" id="column_class_prefix" name="gridui[column_class_prefix]" value="<?=get_option('gridui_prefix');?>" />
    <?php }


    /**
     * Add the row class option
     */
    function option_row_class() { ?>
    <input type="text" id="row_class" name="gridui[row_class]" value="<?=get_option('gridui_row');?>" />
    <?php }


    /**
     * Check if the current page is a post edit page
     *
     * @author Ohad Raz <admin@bainternet.info>
     *
     * @param  string  $new_edit what page to check for accepts new - new post page ,edit - edit post page, null for either
     * @return boolean
     */
    function is_edit_page($new_edit = null){
      global $pagenow;
        //make sure we are on the backend
      if (!is_admin()) return false;


      if($new_edit == "edit")
        return in_array( $pagenow, array( 'post.php',  ) );
        elseif($new_edit == "new") //check for new post page
        return in_array( $pagenow, array( 'post-new.php' ) );
        else //check for either new or edit
        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
      }

      function load_assets() {
        if ($this->is_edit_page()) {

          wp_register_script( 'rangyinputs-jquery', plugins_url('js/rangyinputs_jquery.min.js', __FILE__), array( 'jquery' ), '', true);
          wp_enqueue_script( 'rangyinputs-jquery' );

          wp_register_script( 'grid-ui', plugins_url('js/grid-ui.js', __FILE__), array( 'jquery', 'rangyinputs-jquery' ), '', true);
          wp_enqueue_script( 'grid-ui' );

          wp_register_style( 'grid-ui-style', plugins_url('/grid-ui.css', __FILE__), array(), '', 'all' );
          wp_enqueue_style( 'grid-ui-style' );

        }
      }

      function add_ui() {
        $columns = get_option('gridui_columns');
        $row = get_option('gridui_row');
        $prefix = get_option('gridui_prefix');
        ?>
        <script type="text/javascript">
          var gridUIoptions = {
            'columnsNo' : <?php echo $columns; ?>,
            'rowClass' : '<?php echo $row; ?>',
            'prefixClass' : '<?php echo $prefix; ?>',
            'columnLabel' : '<?php _e("Column", "grid-ui"); ?>'
          };
        </script>

        <div class="postbox-container">
          <div id="grid-ui-ui" class="postbox">

            <div class="handlediv"><br /></div>
            <h3 class="hndle"><?php _e('Grid UI', 'grid-ui'); ?></h3>

            <div class="inside">

              <h4><?php _e('Add a new Row (around selection)', 'grid-ui'); ?></h4>
              <a href="javascript:gridUI.addRow();" class="grid-ui-add-row">&nbsp;</a>

              <h4><?php _e('Add a column (around selection)', 'grid-ui'); ?></h4>
              <div class="grid-ui-column-selector">
                <div class="grid-ui-column-row">
                  <?php for ($i=0; $i < $columns; $i++) {
                    $i_to_show = $i;
                    $i_to_show++;
                    echo '<div class="grid-ui-column" data-column="' . $i_to_show . '">&nbsp;</div>';
                  } ?>
                </div>
              </div>

              <?php
                $preset_fourth = ($columns % 4 != 1) ? $columns/4 : false;
                $preset_third = ($columns % 3 != 1) ? $columns/3 : false;
                $preset_half = ($columns % 2 != 1) ? $columns/2 : false;
              ?>
              <div class="grid-ui-presets">
                <h4><?php _e('Layout Presets', 'grid-ui') ?></h4>
                <?php
                  if ($preset_fourth)
                    echo '<a class="button button-small" href="javascript:gridUI.addPreset(\'' . $preset_fourth . ',' . $preset_fourth . ',' . $preset_fourth . ',' . $preset_fourth . '\');">' . __('4 Columns', 'grid-ui') . '</a>';

                  if ($preset_third)
                    echo '<a class="button button-small" href="javascript:gridUI.addPreset(\'' . $preset_third . ',' . $preset_third . ',' . $preset_third . '\');">' . __('3 Columns', 'grid-ui') . '</a>';

                  if ($preset_half)
                    echo '<a class="button button-small" href="javascript:gridUI.addPreset(\'' . $preset_half . ',' . $preset_half . '\');">' . __('2 Columns', 'grid-ui') . '</a>';
                ?>
              </div>

              <a class="button button-small" href="javascript:gridUI.resetGrid();"><?php _e('Remove grid elements', 'grid-ui'); ?></a>

            </div>
          </div>
        </div>

        <?php }


      }

  /*
   * Instantiate our class
   */
  $grid_ui = new grid_ui();
} // end class exists check