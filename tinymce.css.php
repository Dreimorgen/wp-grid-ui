<?php
header('Content-Type: text/css');
require_once('../../../wp-load.php');

$row = get_option('gridui_row');
$columns = get_option('gridui_columns');
$prefix = get_option('gridui_prefix');
$gutter = '.2%';
$padding = '2px';

for ($i=1; $i <= $columns; $i++) { ?>
.span<?php echo $i; ?> {
  display:inline;
  float:left;
  width: <?php echo (100 / $columns * $i) - ($gutter*$columns) + 1 . '%'; ?>;
  margin: 0 <?php echo $gutter; ?>;
  padding: <?php echo $padding; ?>;
  border:1px dashed #bbb;
}
<?php } ?>
div[class^='span'] img {
  max-width:100%;
  height:auto;
}
#tinymce {
  padding:5px 0 0 0;
}
.row {
  display:inline-block;
  width:101%;
  margin:0 -<?php echo $gutter; ?>;
}