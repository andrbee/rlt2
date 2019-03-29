$(document).ready(function () {
	var urls = [];
	$('.item__auctions a').each(function (key, elem) {
		urls.push($(elem).attr('href'));
	});
	$.ajax({
		url: '/auctions/ajaxMaps',
		method: 'POST',
		data: {
			urls: urls
		},
		success: function (result) {
			if(result.status == 'OK'){
				$('#map').html(result.data['maps']);
				$('#map .map').find('.maptitle').remove();
				$('#map .map').find('br').remove();
				$('#map').html($('#map .map'));
			}
		}
	});
});