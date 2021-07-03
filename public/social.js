(function () {
	var popupCenter = function (url, title, width, height) {
		var popupWidth = width || 640;
		var popupHeight = height || 340;

		var top = window.screenTop || window.screenY;
		var left = window.screenLeft || window.screenX;
		var windowWidth = window.innerWidth;
		var windowHeight = window.innerHeight;
		var popupLeft = left + windowWidth / 2 - popupWidth / 2;
		var popupTop = top + windowHeight / 2 - popupHeight / 2;
		window.open(
			url,
			title,
			"scrollbars=yes, width=" +
				popupWidth +
				", height=" +
				popupHeight +
				", top=" +
				popupTop +
				", left=" +
				popupLeft +
				""
		);
	};

	document
		.querySelector(".share_twitter")
		.addEventListener("click", function (e) {
			e.preventDefault();
			var url = this.href;
			var shareUrl =
				"https://twitter.com/intent/tweet?text=" +
				encodeURIComponent(document.title) +
				"&via=siteName" +
				"&url" +
				encodeURIComponent(url);
			popupCenter(shareUrl, "Partager sur Twitter");
		});

	document
		.querySelector(".share_facebook")
		.addEventListener("click", function (e) {
			e.preventDefault();
			var url = this.href;
			var shareUrl =
				"https://facebook.com/sharer/sharer.php?u=" +
				encodeURIComponent(url);
			popupCenter(shareUrl, "Partager sur Facebook");
		});

	document
		.querySelector(".share_whatsapp")
		.addEventListener("click", function (e) {
			e.preventDefault();
			var url = this.href;
			var shareUrl = "whatsapp://send?text=" + encodeURIComponent(url);
			popupCenter(shareUrl, "Partager sur Whatsapp");
		});
})();
