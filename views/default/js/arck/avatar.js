define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var spinner = require('elgg/spinner');
	require('cropper');

	var $elem = $('input[type="file"].avatar-upload-input');
	var $form = $elem.closest('form');
	var $cropper = $form.find('.avatar-cropper-module');

	if ($('img', $cropper).length) {
		$form.find('input[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
		$cropper.removeClass('hidden');

		$('img', $cropper).cropper({
			aspectRatio: 1,
			data: $('img', $cropper).data(),
			done: function (data) {
				$('input[data-x1]', $form).val(data.x);
				$('input[data-x2]', $form).val((data.x + data.width));
				$('input[data-y1]', $form).val(data.y);
				$('input[data-y2]', $form).val((data.y + data.height));
			}
		});
	}

	$elem.on('change', function (e) {
		var $elem = $(this);
		var $form = $elem.closest('form');
		var $cropper = $form.find('.avatar-cropper-module');

		if ($('img', $cropper).length) {
			$('img', $cropper).cropper('destroy');
			$('img', $cropper).remove();
			$cropper.addClass('hidden');
			$form.find('input[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
		}

		var file = $elem[0].files[0];
		if (!file || !file.type.match(/image.*/)) {
			elgg.register_error(elgg.echo('arck:avatar:invalid_format'));
			return;
		}

		var reader = new FileReader();
		reader.onload = function (e) {

			var img = new Image();
			img.src = reader.result;
			img.alt = file.name;
			img.onloadstart = spinner.start;
			img.onloadend = spinner.stop;
			img.onload = function () {
				$('.avatar-cropper-preview', $cropper).html($(img));

				$form.find('input[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
				$cropper.removeClass('hidden');

				$('img', $cropper).cropper({
					aspectRatio: 1,
					autoCropArea: 0.80,
					done: function (data) {
						$('input[data-x1]', $form).val(data.x);
						$('input[data-x2]', $form).val((data.x + data.width));
						$('input[data-y1]', $form).val(data.y);
						$('input[data-y2]', $form).val((data.y + data.height));
					}
				});
			};
		};

		reader.readAsDataURL(file);
	});

});
