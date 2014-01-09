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
	<span id="loading" style="display:none;">Loading ...</span>
</form>
<div id="lstProgress"></div>

<!-- template -->
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

<!-- script --> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/1.2.1/handlebars.min.js"></script>
<script src="js/jquery.long-process.js"></script>
<script>
$(function() {
	var 
		$btnSubmit = $( '#btnSubmit' ),
		$error = $( '#error' ),
		$loading = $( '#loading' ),
		$lstProgress = $( '#lstProgress' ),
		tplProgress = Handlebars.compile( $( '#tplProgress' ).html() ),
		process = [];
		
	$.longProcess.config({
		beforeProgress: function( id ) {
			var $blkProgress = $( tplProgress({id: id}) ).appendTo( $lstProgress ).slideDown();
			process[id] = {
				$blkProgress: $blkProgress,
				$tasks: $blkProgress.find( '.tasks' ),
				$progress: $blkProgress.find( '.progress' ),
				$status: $blkProgress.find( '.status' ),
			};
		},
		inProgress: function( data ) {
			process[data.key].$tasks.text(data.tasks.current + '/' + data.tasks.total);
			process[data.key].$progress.progressbar({value: parseInt(data.weight.current / data.weight.total * 100, 10)});
		},
		afterProgress: function(id, complete) {
			process[id].$status.text(complete ? 'Your request has been completed!' : 'End with exception!');
			setTimeout(function() {
				process[id].$blkProgress.slideUp();
				delete(process[id]);
			}, 3000);
		},
		taskMessage: function(i, data) {
			console.log(i, data);
		},
		taskFail: function(i, errorThrown) {
			console.error(i, errorThrown);
		},
	});
	
	$.longProcess.checkUnfinished({url: 'checkProgress.php'});
		
	$btnSubmit.on('click', function(e) {
		e.preventDefault();
		$.longProcess.run({
			processAjax: {
				url: 'process.php',
				data: $( '#frmProgress' ).serialize(),
				beforeSend: function() {
					$loading.show();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					var error = errorThrown;
					if ($.trim(jqXHR.responseText)) error += ' - ' + jqXHR.responseText;
					$error.text( error ).slideDown();
					setTimeout(function() {
						$error.slideUp();
					}, 3000);
				},
				complete: function() {
					$loading.hide();
				}
			}
		});
	});
});
</script>
</body>
</html>