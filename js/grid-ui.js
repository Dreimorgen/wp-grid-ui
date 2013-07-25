function wrapWithContainer(classes) {
  var form = jQuery('#content');
  form.surroundSelectedText('\n<div class=\'' + classes + '\'>','</div>\n');
}

function addRow(event) {
  wrapWithContainer(gridUiOptions.rowClass);
}


function setColumns(event) {
  var col = jQuery(this).attr('data-column');
  wrapWithContainer(gridUiOptions.prefixClass+col);
}

function resetGrid(event) {
  var content = jQuery('#content').text(),
    regex = /(<div\ class\=\"(span(\d+)|row)\">|<\/div>)/ig,
    cleanedContent = content.replace(regex, "");
  jQuery('#content_ifr').contents().find('#tinymce').html(cleanedContent);
  jQuery('#content').text(cleanedContent);
}


function markColumns(event) {
  jQuery(this).addClass('marked-column');
  jQuery(this).prevAll('.grid-ui-column').addClass('marked-column');
}

function resetMarkedColumns(event) {
  jQuery('.grid-ui-column').removeClass('marked-column');
}


jQuery(document).ready(function($){

  $('.grid-ui-row-selector').on('click', addRow);

  $('.grid-ui-column').on({
    'click' : setColumns,
    'mouseenter' : markColumns,
    'mouseleave' : resetMarkedColumns
  });

  $('#grid-ui-ui h2').on('click', function() {
    $('#grid-ui-ui fieldset').toggleClass('collapsed-fieldset');
    $('#grid-ui-ui').toggleClass('options-are-visible');
  });

  $('.grid-ui-reset').on('click', resetGrid);

});