/**
 * @name jquery.long-process.js
 * @author Au Yeung Chun Ming
 * @version 0.9.1
 * @copyright free for use
 * @document https://github.com/ayming/php-long-process
 */

(function( $ ) {
	$.longProcess = (function() {
		var 
			settings = {
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
				beforeProgress: function() {},
				inProgress: function() {},
				afterProgress: function() {},
				taskMessage: function() {},
				taskSuccess: function() {},
				taskFail: function() {},
				refreshDelay: 500
			};
		
		function config( options ) {
			// overwrite default setting
			settings = $.extend( true, settings, options );
		}
		
		// handle progress of process
		function process( options ) {
			// validation
			if (!options.processAjax.url) 
				return console.error('Process url is missing');
			if (options.refreshDelay && options.refreshDelay > 1000) 
				return console.error('Refresh delay must be smaller than 1 second');
			
			// extend setting
			config( options );
			var pcAjax = $.extend(true, {}, settings.processAjax);
				
			pcAjax.success = function( data ) {
				// run custom success callback
				settings.processAjax.success.apply(this, arguments);
				// start progress checking
				progress(data.key, data.progress, $.extend(true, {}, settings.progressAjax));
			};
			
			$.ajax(pcAjax);
		}
		
		// handle progress of process
		function progress(key, file, ajax) {
			var taskIndex = 0;
			// beforeProgress callback
			settings.beforeProgress( key );
			
			// overwrite progress ajax setting
			ajax.url = file;
			ajax.success = function( data ) {
				// custom success callback
				settings.progressAjax.success.apply(this, arguments);
				// inProgress callback
				settings.inProgress( data );
				
				for (var i = taskIndex + 1; i <= data.tasks.current; i++) {
					// taskMessage callback
					if (data.message[i]) settings.taskMessage(i, data.message[i]);
					// taskFail callback
					if (data.fail[i]) settings.taskFail(i, data.fail[i]);
					// update task index
					taskIndex = i;
				}
				
				if (data.running && data.tasks.current < data.tasks.total) {
					setTimeout(function() { $.ajax(ajax) }, settings.refreshDelay);
				} else {
					// afterProgress callback
					settings.afterProgress(key, (data.tasks.current == data.tasks.total));
				}
			};
			
			$.ajax(ajax);
		}
		
		// check all unfinished process at begin
		function checkUnfinishedProcess( options ) {
			var ajaxSettings = $.extend( true, {
				dataType: 'json',
				type: 'post',
				success: function() {}
			}, options );
			
			ajaxSettings.success = function( data ) {
				if (options.success) options.success.apply(this, arguments);
				$.each(data, function(i, file) {
					$.get(file, function(data) {
						progress(data.key, file, $.extend(true, {}, settings.progressAjax));
					});
				});
			};
			
			$.ajax(ajaxSettings);
		}
		
		return {
			config: config,
			checkUnfinished: checkUnfinishedProcess,
			run: process, // start the process
		};
	})();
})( jQuery );