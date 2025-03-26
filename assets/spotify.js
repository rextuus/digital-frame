import './styles/spotify.scss';
import './bootstrap';

var Spotify = require('spotify-web-api-js');

const spotifyMode = 2;
let currentMode = 2;
let lastUrl = "";

// set token to spotify api
var spotify = new Spotify();
spotify.setAccessToken(token);

function change() {
    setMode();

    if (currentMode === spotifyMode) {
        spotify.getMyCurrentPlayingTrack().then(
            function (data) {
                let url = data['item']['album']['images'][0]['url'];
                if (lastUrl === url) {
                    return;
                }
                lastUrl = url;
                // ask backend for dominant color
                let request = new XMLHttpRequest();
                request.open(('GET'), "http://127.0.0.1:8000/configuration/background?url=" + url);
                request.send();
                request.onload = () => {
                    if (request.status === 200) {
                        let backgroundColor = JSON.parse(request.response);
                        // document.body.style.backgroundColor = 'rgb(' + backgroundColor.join(',') + ')';
                        calculateImagePos();
                        setGradient('rgb(' + backgroundColor.join(',') + ')', 'rgb(0,0,0)');
                    } else {
                        window.location.replace("http://127.0.0.1:8000/stage");
                        console.log('error ${request.status} ${request.statusText}');
                    }
                }

                document.getElementById('the-image').src = data['item']['album']['images'][0]['url'];
            },
            function (err) {
                console.error(err);
            }
        );
    } else {
        window.location.replace("http://127.0.0.1:8000/stage");
    }
}

function setGradient(color1, color2) {
    console.log(color1, color2);

    let css = document.querySelector("h4");
    let body = document.getElementById("gradient");

    if (!body) {
        console.error("The element with id 'gradient' does not exist.");
        return;
    }

    body.style.background =
        "radial-gradient("
        + color1
        + ", "
        + color2
        + ")";

    // Only update css if <h4> exists
    if (css) {
        css.textContent = body.style.background + ";";
    } else {
        console.warn("The <h4> element does not exist in the DOM.");
    }
}


window.onload = function () {
    // after 60 minutes accesToken needs to be refreshed
    let minutes = 61;
    setTimeout(() => {
        window.location.replace("http://127.0.0.1:8000/stage");
    }, minutes * 60 * 1000);

    setInterval(change, 5000);
};

function setMode() {
    let request = new XMLHttpRequest();
    request.open(('GET'), "http://127.0.0.1:8000/configuration/change");
    request.send();
    request.onload = () => {
        if (request.status === 200) {
            currentMode = JSON.parse(request.response)['mode'];
        } else {
            console.log('error ${request.status} ${request.statusText}');
        }
    }
}

function calculateImagePos(){
    let image = document.getElementById('container');
    let distanceFromTop = image.offsetTop;
    let distanceFromBottom = window.innerHeight - (image.offsetTop + image.offsetHeight);
    let total = distanceFromTop + distanceFromBottom;
    image.style.marginTop = (total/2) + "px";//(screenHeight - height) / 2;
}