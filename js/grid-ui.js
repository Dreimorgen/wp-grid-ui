// generic helper function to wrap the selection with an html wrapper with a class we pass as argument
function wrapWithContainer(classes) {
  // for tinymce view
  if (jQuery('#content_ifr').length) {
    var tOutput = '\n<div class=\'' + classes + '\'>' + window.tinymce.activeEditor.selection.getContent({format: 'raw'}) + '</div>\n';
    window.tinymce.activeEditor.selection.setContent(tOutput);
  }
  // for the code view
  jQuery('#content').surroundSelectedText('\n<div class=\'' + classes + '\'>','</div>\n');

}

// add one row
function addRow(event) {
  wrapWithContainer(gridUiOptions.rowClass);
}

// set the column depending on the data-column attribute of the event.target
function setColumns(event) {
  var col = jQuery(this).attr('data-column');
  wrapWithContainer(gridUiOptions.prefixClass+col);
}

// reset the whole grid and remove all wrapper elements
function resetGrid(event) {
  var content = jQuery('#content').text(),
    regex = /(<div\ class\=\"(span(\d+)|row)\">|<\/div>)/ig,
    cleanedContent = content.replace(regex, "");
  jQuery('#content_ifr').contents().find('#tinymce').html(cleanedContent);
  jQuery('#content').text(cleanedContent);
}

// function addPreset(args) {
//   console.log(args);
// }

// in the ui, mark this and all previus columns
function markColumns(event) {
  jQuery(this).addClass('marked-column');
  jQuery(this).prevAll('.grid-ui-column').addClass('marked-column');
}

// in the ui, reset all marked columns
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

  // $('.grid-ui-preset-three-three').on('click', function(event) {
  //   addPreset.apply(this, args);
  // });

});