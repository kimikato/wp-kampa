jQuery(function($) {

	// ページがロードされたら実行する
	$(document).ready(function() {
		var kampa_key = kampa_key_json['kampa_key'];

		$.jsonp({
			url: 'http://kampa-proxy-api.herokuapp.com/kampa/' + kampa_key + '',
			dataType: 'jsonp',
			jsonp: 'callback',
			callbackParameter: 'callback',
			cache: true,
			pageCache: true,
			success: function(json) {
				for(var i in json.data) {
					// slices
					var slides = $('.slides');

					// Kampa! Area
					var kampa_box = $('<div/>').attr('class', 'kampa-box');
					// Kampa! Image
					var kampa_image = $('<div/>').attr('class', 'kampa-image');
					// Amazon Image
					var amazon_image = $('<img/>').attr({'src': json.data[i].pic, 'alt': json.data[i].title});
					// Kampa! Link
					$('<a/>')
						.attr({'href': json.data[i].kmp_page, 'title': json.data[i].title, '_target': 'blank'})
						.append(amazon_image)
						.appendTo(kampa_image);

					// Kampa! Progress
					var kampa_pg = $('<div/>').attr('class', 'kampa-progress');

					// ProgressBar
					var pg = $('<div/>').attr('id', 'pg-' + json.data[i].item_bs);
					var pgValue = pg.find('.ui.progressbar-value');
					var pgLabel = $('<div/>').attr('class', 'progress-label');

					// Set Progressbar Value
					pg.progressbar({ value: false });
					pg.progressbar({ value: json.data[i].percentage });
					pgValue.css({'backend': '#c6c6c6'});
					pgLabel.text( json.data[i].percentage + ' %' );

					pgLabel.appendTo(pg);
					pg.appendTo(kampa_pg);

					//
					kampa_image.appendTo(kampa_box);
					kampa_pg.appendTo(kampa_box);

					kampa_box.appendTo(slides);
				}
			},
			complete: function() {
				$("#kampa").jContent({
						width: '220px',
						height: '220px',
						orientation: 'horizontal',
						easing: 'easeOutCirc',
                        duration: 5000,
                        auto: true,
                        direction: 'next', //or 'prev'
                        pause: 2500,
                        pause_on_hover: true});
			}
		});


	});
});