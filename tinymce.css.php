<?php
require_once('../../../wp-load.php');
$row = get_option('gridui_row');
$columns = get_option('gridui_columns');
$prefix = get_option('gridui_prefix');
$gutter = '.25%';

for ($i=1; $i <= $columns; $i++) { ?>
.span<?php echo $i; ?> {
  display:inline;
  float:left;
  background:red;
  width: <?php echo ((100-$columns*$gutter) / $columns * $i)-$gutter . '%'; ?>;
  margin: 0 <?php echo $gutter; ?>;
}

<?php } ?>
.row {
  display:inline-block;
  width:101%;
  margin:0 -<?php echo $gutter; ?>;
}