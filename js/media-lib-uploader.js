jQuery(document).ready(function($){

  var mediaUploader;

  $('#upload-button').click(function(e) {
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader =  wp.media({
      title: 'Add Attachments',
      button: {
      text: 'Choose Files'
    }, multiple: true });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function() {
		//media_uploader.on("insert", function(){

		
        var length = mediaUploader.state().get("selection").length;
        var files = mediaUploader.state().get("selection").models

		var arr_file_url = []
        for(var iii = 0; iii < length; iii++)
        {
            //var image_url = files[iii].changed.url;
			arr_file_url.push( files[iii].changed.url ); 
			$('#attachment-container').append( '<a href="' + files[iii].changed.url + '" target="_blank">' + files[iii].changed.title + ' (' + files[iii].changed.url + ')' + '</a><br/>' )
			//var image_caption = files[iii].changed.caption;
            //var image_title = files[iii].changed.title;
        }
		//console.log( arr_file_url );
		var prev_attachments = $("#attachments").val();
		if(jQuery.trim(prev_attachments).length > 0) {
			$('#attachments').val( prev_attachments + ',' + arr_file_url.join() );
		}
		else {
			$('#attachments').val( arr_file_url.join() );
		}
		
    //});
		/*
		  console.log(mediaUploader.state().get('selection'));
		  attachment = mediaUploader.state().get('selection').first().toJSON();
		  $('#attachments').val(attachment.url);
		*/
    });
    // Open the uploader dialog
    mediaUploader.open();
  });

});