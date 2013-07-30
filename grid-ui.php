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
      // if this is admin
      if ( is_admin() ){

        // load the textdomain
        load_plugin_textdomain('grid-ui', false, dirname(plugin_basename(__FILE__)) . '/lang/');

        // settings
        add_action( 'admin_menu', array( $this, 'add_settings_menu_item' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // edit forms
        add_action( "admin_init", array( $this, "load_assets" ) );
        add_action( "edit_form_after_title", array( $this, "add_ui" ) );

        // set default options
        $this->set_default_options();

        // add tinyMCE css
        add_filter( 'mce_css', array( $this, 'grid_css_for_mce' ) );
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
        __('<br />Here you can set your grid system<br />', 'grid-ui'),
        false,
        'grid-ui-settings'
        );

      add_settings_section(
        'grid-ui-settings-classes',
        __('<br />Here you can set the classes which fit your grid system<br />', 'grid-ui'),
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
        'grid_system',
        __( 'Grid system', 'grid-ui' ),
        array( $this, 'option_grid_system'),
        'grid-ui-settings',
        'grid-ui-settings-basic'
        );

      add_settings_field(
        'grid_css',
        __( 'Use the CSS of this plugin', 'grid-ui' ),
        array( $this, 'option_grid_css'),
        'grid-ui-settings',
        'grid-ui-settings-basic'
        );


      add_settings_field(
        'column_classes',
        __( 'Column classes', 'grid-ui' ),
        array( $this, 'option_column_classes'),
        'grid-ui-settings',
        'grid-ui-settings-classes'
        );

      add_settings_field(
        'row_class',
        __( 'Row class', 'grid-ui' ),
        array( $this, 'option_row_class'),
        'grid-ui-settings',
        'grid-ui-settings-classes'
        );
    }



    /**
     * Load the Assets for the editing UI
     */
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

    /**
     * Add the Ui to the edit page
     */
    function add_ui() {
      $columns = get_option('gridui_columns');
      $row = get_option('gridui_row');
      $prefix = get_option('gridui_prefix');
      $column_class = get_option('gridui_column_class'); ?>

      <script type="text/javascript">
        var gridUIoptions = {
          'columnsNo' : <?php echo $columns; ?>,
          'rowClass' : '<?php echo $row; ?>',
          'columnClasses' : '<?php echo $column_class; ?>',
          'columnLabel' : '<?php _e("Column", "grid-ui"); ?>'
        };
      </script>

      <div class="postbox-container">
        <div id="grid-ui-ui" class="postbox closed">

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
    <?php
    }



    /**
     * Set the default options on installation
     */
    function set_default_options() {
      if (get_option('gridui_grid_css') === FALSE) {
        add_option( 'gridui_grid_css', 'on' );
      }
      if (get_option('gridui_columns') === FALSE) {
        add_option( 'gridui_columns', 12 );
      }
      if (get_option('gridui_column_class') === FALSE) {
        add_option( 'gridui_column_class', 'span1, span2, span3, span4, span5, span6, span7, span8, span9, span10, span11, span12', 'grid-ui' );
      }
      if (get_option('gridui_row') === FALSE) {
        add_option( 'gridui_row', 'row' );
      }
      if (get_option('gridui_grid_system') === FALSE) {
        add_option( 'gridui_grid_system', __( 'Default', 'grid-ui', 'grid-ui' ) );
      }
    }





    /**
     * Check the input from the options page
     */
    public function check_settings($input){
      $columns = $input['number_of_columns'];
      $grid_system = $input['grid_system'];
      $grid_css = $input['grid_css'];
      $column_class = $input['column_classes'];
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

      // if grid system is set, set the option in the db
      if (isset($grid_system)) {
        if (get_option('gridui_grid_system') === FALSE) {
          add_option('gridui_grid_system', $grid_system);
        } else {
          update_option('gridui_grid_system', $grid_system);
        }


        // if the gridsystem is not custom, set these settings for the other options
        if ($grid_system == __('Bootstrap 2', 'grid-ui')) {
          $colprefix = 'span';
          $rowclass = 'row';
          // set the row class
          if (get_option('gridui_row') === FALSE) {
            add_option('gridui_row', $rowclass);
          } else {
            update_option('gridui_row', $rowclass);
          }
          // write the column class
          $colclass = '';
          for ($i=1; $i <= (int) $columns; $i++) {
            $colclass .= $colprefix . $i;
            if ($i < $columns) {
              $colclass .= ', ';
            }
          }
          // set it
          if (get_option('gridui_column_class') === FALSE) {
            add_option('gridui_column_class', $colclass);
          } else {
            update_option('gridui_column_class', $colclass);
          }


        } else if ($grid_system == __('Skeleton', 'grid-ui')) {
          $rowclass = 'container';
          // set the row class
          if (get_option('gridui_row') === FALSE) {
            add_option('gridui_row', $rowclass);
          } else {
            update_option('gridui_row', $rowclass);
          }
          // Skeleton is always twelve columns
          $columns = 12;
          if (get_option('gridui_columns') === FALSE) {
            add_option('gridui_columns', (int) $columns);
          } else {
            update_option('gridui_columns', (int) $columns);
          }
          $colclass = 'one column, two columns, three columns, four columns, five columns, six columns, seven columns, eight columns, nine columns, ten columns, eleven columns, twelve columns';
          // set it
          if (get_option('gridui_column_class') === FALSE) {
            add_option('gridui_column_class', $colclass);
          } else {
            update_option('gridui_column_class', $colclass);
          }



        } else if ($grid_system == __('Foundation', 'grid-ui')) {
          $rowclass = 'row';
          // set the row class
          if (get_option('gridui_row') === FALSE) {
            add_option('gridui_row', $rowclass);
          } else {
            update_option('gridui_row', $rowclass);
          }
          // Skeleton is always twelve columns
          $columns = 12;
          if (get_option('gridui_columns') === FALSE) {
            add_option('gridui_columns', (int) $columns);
          } else {
            update_option('gridui_columns', (int) $columns);
          }
          $colclass = 'large-1 columns, large-2 columns, large-3 columns, large-4 columns, large-5 columns, large-6 columns, large-7 columns, large-8 columns, large-9 columns, large-10 columns, large-11 columns, large-12 columns';
          // set it
          if (get_option('gridui_column_class') === FALSE) {
            add_option('gridui_column_class', $colclass);
          } else {
            update_option('gridui_column_class', $colclass);
          }


        } else if ($grid_system == __('Default', 'grid-ui')) {

          $rowclass = 'row';

          // set the row class
          if (get_option('gridui_row') === FALSE) {
            add_option('gridui_row', $rowclass);
          } else {
            update_option('gridui_row', $rowclass);
          }
          $columns = 12;
          if (get_option('gridui_columns') === FALSE) {
            add_option('gridui_columns', (int) $columns);
          } else {
            update_option('gridui_columns', (int) $columns);
          }

          $colclass = 'span1, span2, span3, span4, span5, span6, span7, span8, span9, span10, span11, span12';
          // set it
          if (get_option('gridui_column_class') === FALSE) {
            add_option('gridui_column_class', $colclass);
          } else {
            update_option('gridui_column_class', $colclass);
          }
        }
      }

      if ( $grid_css === 'on' ) {
        if (get_option('gridui_grid_css') === FALSE) {
          add_option('gridui_grid_css', $grid_css);
        } else {
          update_option('gridui_grid_css', $grid_css);
        }
      } else {
        if (get_option('gridui_grid_css') === FALSE) {
          add_option('gridui_grid_css', 'off');
        } else {
          update_option('gridui_grid_css', 'off');
        }
      }

      // if row is set and the grid system is set to "custom"
      if ( isset($row) && $grid_system == __('Custom', 'grid-ui') ) {
        if (get_option('gridui_row') === FALSE) {
          add_option('gridui_row', $row);
        } else {
          update_option('gridui_row', $row);
        }
      }

      // if column class is set and the grid system is set to "custom"
      if ( isset($column_class) && $grid_system == __('Custom', 'grid-ui') ) {

        if (get_option('gridui_column_class') === FALSE) {
          add_option('gridui_column_class', $column_class);
        } else {
          update_option('gridui_column_class', $column_class);
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
    <?php
    }


    /**
     * Add the column number option
     */
    function option_columns() { ?>
    <input type="number" id="number_of_columns" name="gridui[number_of_columns]" value="<?=get_option('gridui_columns');?>" />
    <?php }

    /**
     * Add the grid system option
     */
    function option_grid_system() {
      $grid_system[0] = __('Default', 'grid-ui');
      $grid_system[1] = __('Custom', 'grid-ui');
      $grid_system[2] = __('Bootstrap 2', 'grid-ui');
      $grid_system[3] = __('Skeleton', 'grid-ui');
      $grid_system[4] = __('Foundation', 'grid-ui');
      $selected_system = get_option('gridui_grid_system');
      ?>

      <select id="grid_system" name="gridui[grid_system]">
        <?php
        for ($i=0; $i < count($grid_system); $i++) {
          if ($grid_system[$i] !== $selected_system) {
            echo '<option>' . $grid_system[$i] . '</option>';
          } else {
            echo '<option selected="selected">' . $grid_system[$i] . '</option>';
          }
        }
        ?>
      </select>
      <?php
    }

    /**
     * Add the Option to use the plugin frontend css file
     */
    function option_grid_css() {
      if (get_option('gridui_grid_system', true) === __('Default', 'grid-ui') ) { ?>
        <input type="checkbox" id="grid_css" name="gridui[grid_css]" <?php if (get_option('gridui_grid_css') === 'on') { echo 'checked="checked"'; }?> />
      <?php
      } else { ?>
        <span class="description"><?php _e('This Option is only available if the Grid system is set to Default.', 'grid-ui'); ?></span>
      <?php
      }
    }


    /**
     * Add the column prefix option
     */
    function option_column_classes() { ?>
    <input type="text" id="column_classes" name="gridui[column_classes]" value="<?=get_option('gridui_column_class');?>" />
    <span class="description"><?php _e('Classes should be seperated by kommas. There should be as many as there are columns in your grid.'); ?></span>
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


    /**
     * Add the TinyMCE css for the visual content editor
     */
    function grid_css_for_mce($wp) {
      $wp .= ',' . plugins_url('grid-ui/tinymce.css.php');
      return $wp;
    }


  }

  /*
   * Instantiate our class
   */
  $grid_ui = new grid_ui();

  /**
   * Load the frontend css for the Default grid
   */
  function load_grid_css() {
    if (get_option('gridui_grid_css') === 'on') {
      wp_register_style( 'gridui-grid', plugins_url('grid-ui/gridui-css.css'), array(), '', 'all' );
      wp_enqueue_style( 'gridui-grid' );
    }
  };

  // load the css if this option was set
  add_action('init', 'load_grid_css', 15);

} // end class exists check