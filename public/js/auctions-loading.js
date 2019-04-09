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
		success: [
			function (result) {
				if(result.status === 'OK'){
					var maps = result.data.maps;
					$.each(maps,function (key,value) {
						var mapItem = $('.map__item').eq(key);
						mapItem.html($(value).find('.map').html());
						mapItem.find('br').remove();
						mapItem.find('.maptitle').remove();
					});
				}
			}
		]
	});
});