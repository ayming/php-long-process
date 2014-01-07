<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
</head>
<body>
<h1>Simulating heavy process</h1>
<!-- form -->
<form id="frmProgress">
	<div id="error" style="color: red; display:none;"></div>
	<table>
		<tr>
			<td>
				<label for="txtID">#ID: </label>
			</td>
			<td>
				<input type="text" name="id" value="1" id="txtID" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="txtNumber">#Tasks: (Default=10)</label>
			</td>
			<td>
				<input type="text" name="number" value="10" id="txtNumber" />
			</td>
		</tr>
	</table>
  <button type="submit" name="submit" value="1" id="btnSubmit">Submit</button>
	<div id="loading" style="display:none;">Loading ...</div>
</form>
<div id="lstProgress"></div>

<!-- script --> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/1.2.1/handlebars.min.js"></script>
<script id="tplProgress" type="text/x-handlebars-template">
	<fieldset id="progress-{{id}}" style="display: none;">
		<legend>#ID: {{id}}</legend>
		<table style="width: 100%;">
			<tr>
				<td>statue:</td>
				<td><span class="status">Your request is processing...</span></td>
			</tr>
			<tr>
				<td>tasks:</td>
				<td><span class="tasks">Your request is processing...</span></td>
			</tr>
			<tr>
				<td>progress:</td>
				<td><div class="progress"></div></td>
			</tr>
		</table>
	</fieldset>
</script>
<script>
(function( $ ) {
	$.longProcess = function( options ) {
		// validation
		if (!options.processAjax.url) 
			return console.error('Process url is missing');
		if (options.refreshDelay && options.refreshDelay > 1000) 
			return console.error('Refresh delay must be smaller than 1 second');
		
		// overwrite default setting
		var 
			settings = $.extend( true, {
				processAjax: {
					dataType: 'json',
					type: 'post',
					success: function() {},
					error: function() {},
				},
				progressAjax: {
					dataType: 'json',
					success: function() {},
					error: function() {},
				},
				refreshDelay: 500
			}, options ),
			pcAjax = $.extend(true, {}, settings.processAjax),
			pgAjax = $.extend(true, {}, settings.progressAjax);
		
		// handle progress of process
		function process() {
			pcAjax.success = function( data ) {
				settings.processAjax.success.apply(this, arguments);
				progress( data.progress );
			};
			
			$.ajax(pcAjax);
		}
		
		// handle progress of process
		function progress( file ) {
			pgAjax.url = file;
			pgAjax.success = function( data ) {
				settings.progressAjax.success.apply(this, arguments);
				if (data.running && data.tasks.current < data.tasks.total) {
					setTimeout(function() { $.ajax(pgAjax) }, settings.refreshDelay);
				}
			};
			
			$.ajax(pgAjax);
		}
		
		// start the process
		process();
	}
})( jQuery );
</script>
<script>
$(function() {
	var 
		$btnSubmit = $( '#btnSubmit' ),
		$error = $( '#error' ),
		$loading = $( '#loading' ),
		$lstProgress = $( '#lstProgress' ),
		tplProgress = Handlebars.compile( $( '#tplProgress' ).html() );
		
	$btnSubmit.on('click', function() {
		var 
			id = $( '#txtID' ).val(),
			$blkProgress = $( tplProgress({id: id}) ),
			$tasks = $blkProgress.find( '.tasks' ),
			$progress = $blkProgress.find( '.progress' ),
			$status = $blkProgress.find( '.status' );
		
		$.longProcess({
			processAjax: {
				url: 'progress.php',
				data: $( '#frmProgress' ).serialize(),
				beforeSend: function() {
					$loading.show();
				},
				success: function() {
					$blkProgress.appendTo( $lstProgress ).slideDown();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					var error = errorThrown;
					if (jqXHR.responseText) error += ' - ' + jqXHR.responseText;
					$error.text( error ).slideDown();
					setTimeout(function() {
						$error.slideUp();
					}, 3000);
				},
				complete: function() {
					$loading.hide();
				}
			},
			progressAjax: {
				success: function( data ) {
					$tasks.text(data.tasks.current + '/' + data.tasks.total);
					$progress.progressbar({value: parseInt(data.weight.current / data.weight.total * 100, 10)});
					if (data.tasks.current == data.tasks.total) {
						$status.text('Your request has been completed!');
						setTimeout(function() {
							$blkProgress.slideUp();
						}, 3000);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error(jqXHR);
					console.error(textStatus);
					console.error(errorThrown);
				}
			},
			taskSuccess: function(i, data) {
				
			},
			taskError: function(i, errorThrown) {
				
			},
		});
		return false;
	});
});
</script>
</body>
</html>
<?php
$cacheFolder = $_SERVER['DOCUMENT_ROOT'] . '/cache/';
//file_put_contents($cacheFolder.'log.txt', print_r(array('a'), true));
@unlink($cacheFolder.'progress-c4ca4238a0b923820dcc509a6f75849b.json');
/*function test($a, $b) {
	print_r($a);
	print_r($b);
}
call_user_func_array('test', array('', ''))*/