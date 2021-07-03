function loadFile(filePath) {
	var result = null;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET", filePath, false);
	xmlhttp.send();
	if (xmlhttp.status == 200) {
		result = xmlhttp.responseText;
	}
	return result;
}

var song = new Audio();
var slider = document.querySelector("#slider");
var fill = document.querySelector(".bar .fill");
song.type = "audio/mpeg" || "audio/mpeg-4";
var songSrc = document.querySelector("audio").src;
song.src = songSrc;
// document.querySelector("#placeholder").textContent = loadFile(songSrc);

function skip(time) {
	if (time == "back") {
		song.currentTime = song.currentTime - 5;
	} else if (time == "fwd") {
		song.currentTime = song.currentTime + 5;
	}
}

function playPause(control) {
	if (song.paused) {
		song.play();
		control.classList.replace("fa-play-circle", "fa-pause-circle");
		control.parentNode.classList.add("rotation");
	} else {
		song.pause();
		control.classList.replace("fa-pause-circle", "fa-play-circle");
		control.parentNode.classList.remove("rotation");
	}
}

function setPos(pos) {
	song.currentTime = pos;
}

function formatTime(time) {
	var hours = Math.floor(time / 3600);
	var mins = Math.floor((time % 3600) / 60);
	var secs = Math.floor(time % 60);

	if (secs < 10) {
		secs = "0" + secs;
	}

	if (hours) {
		if (mins < 10) {
			mins = "0" + mins;
		}

		return hours + ":" + mins + ":" + secs; // hh:mm:ss
	} else {
		return mins + ":" + secs; // mm:ss
	}
}

song.addEventListener("timeupdate", function () {
	document.querySelector(".current-time span").textContent = formatTime(
		song.currentTime
	);
	document.querySelector(".total-time span").textContent = formatTime(
		song.duration
	);

	curtime = Math.ceil(song.currentTime);
	slider.max = song.duration;
	slider.value = curtime;
	fraction = song.currentTime / song.duration;
	percent = Math.ceil(fraction * 100);
	fill.style.width = percent + "%";
	if (song.ended) {
		var play = document.querySelector("#play");
		play.classList.replace("fa-pause-circle", "fa-play-circle");
		song.pause();
		play.parentNode.classList.remove("rotation");
		song.currentTime = 0;
		slider.value = 0;
	}
});
