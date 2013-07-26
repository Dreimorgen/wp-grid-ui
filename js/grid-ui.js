var gridUI = {
  // the options are defined via php on the edit page itself
  options: gridUIoptions,

  // generic helper function to wrap the selection with an html wrapper with a class we pass as argument
  addWrapper: function(classes) {
    // for tinymce view
    if (jQuery('#content_ifr').length) {
      var tOutput = '\n<div class=\'' + classes + '\'>' + window.tinymce.activeEditor.selection.getContent({format: 'raw'}) + '</div>\n';
      window.tinymce.activeEditor.selection.setContent(tOutput);
    }
    // for the code view
    jQuery('#content').surroundSelectedText('\n<div class=\'' + classes + '\'>','</div>\n');
  },

  // add one row
  addRow: function(event) {
    gridUI.addWrapper(gridUI.options.rowClass);
  },

  // set the column depending on the data-column attribute of the event.target
  setColumns: function(event) {
    var col = jQuery(this).attr('data-column');
    gridUI.addWrapper(gridUI.options.prefixClass+col);
  },

  // reset the whole grid and remove all wrapper elements
  resetGrid: function(event) {
    var content = jQuery('#content').val(),
      regex = new RegExp('(<div class\=\"(' + gridUI.options.prefixClass + '(\d+)|' + gridUI.options.rowClass + ')\">|<\/div>)', 'ig'),
      cleanedContent = content.replace(regex, "");

    console.log(regex);

    jQuery('#content_ifr').contents().find('#tinymce').html(cleanedContent);
    jQuery('#content').val(cleanedContent);
  },

  // add a column preset
  // args is a string representing the columns as numbers for their widths sperated by commas
  // i.e. two six-columns are '6,6'
  addPreset: function(args) {
    var cols = args.split(',');
    var output = '<div class=\"' + gridUI.options.rowClass + '\">\n';
    for (var i = cols.length - 1; i >= 0; i--) {
      output = output + '\ \ <div class=\"' + gridUI.options.prefixClass + cols[i] + '\">' + gridUI.options.columnLabel + '<\/div>\n';
    };
    output = output + '<\/div>';
    if (jQuery('#content_ifr').length) {
      window.tinymce.activeEditor.setContent(output);
    }
    jQuery('#content').val(output);
  },

  // in the ui, mark this and all previus columns
  markColumns: function(event) {
    jQuery(this).addClass('marked-column');
    jQuery(this).prevAll('.grid-ui-column').addClass('marked-column');
  },

  // in the ui, reset all marked columns
  resetMarkedColumns: function(event) {
    jQuery('.grid-ui-column').removeClass('marked-column');
  }
};


jQuery(document).ready(function($){
  $('.grid-ui-column').on({
    'click' : gridUI.setColumns,
    'mouseenter' : gridUI.markColumns,
    'mouseleave' : gridUI.resetMarkedColumns
  });
});