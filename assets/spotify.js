/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/spotify.css';

// start the Stimulus application
import './bootstrap';

var Spotify = require('spotify-web-api-js');

let mode = 0;
let lastUrl = "";

// set token to spotify api
var spotify = new Spotify();
spotify.setAccessToken(token);
//
// var mode = 0;
//
// var photo = unsplash.photos.get(
//     { photoId: 'pFqrYbhIAXs' }
// ).then(result => {
//     if (result.errors) {
//         // handle error here
//         console.log('error occurred: ', result.errors[0]);
//     } else {
//         // handle success here
//         photo = result.response;
//         console.log(photo);
//         return photo;
//     }
// });
// console.log(photo);
//
function change() {
    // let request = new XMLHttpRequest();
    // request.open(('GET'), "https://127.0.0.1:8000/stage/change");
    // request.send();
    // request.onload = () => {
    //     console.log(request);
    //     if (request.status == 200){
    //         mode = JSON.parse(request.response)['mode'];
    //     }else{
    //         console.log('error ${request.status} ${request.statusText}');
    //     }
    // }

    setMode();

    if (mode === 0) {
        spotify.getMyCurrentPlayingTrack().then(
            function (data) {
                let url = data['item']['album']['images'][0]['url'];
                if (lastUrl === url) {
                    return;
                }
                lastUrl = url;
                // ask backend for dominant color
                let request = new XMLHttpRequest();
                request.open(('GET'), "https://127.0.0.1:8000/configuration/background?url=" + url);
                request.send();
                request.onload = () => {
                    console.log(request);
                    if (request.status === 200) {
                        let backgroundColor = JSON.parse(request.response);
                        console.log(backgroundColor);
                        // document.body.style.backgroundColor = 'rgb(' + backgroundColor.join(',') + ')';
                        setGradient('rgb(' + backgroundColor.join(',') + ')', 'rgb(0,0,0)');
                    } else {
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
        window.location.replace("https://127.0.0.1:8000/stage");
    }
}

function setGradient(color1, color2) {
    console.log(color1, color2);

    let css = document.querySelector("h4");
    let body = document.getElementById("gradient");
    // let grad =
    //     "linear-gradient(to right, "
    //     + color1
    //     + ", "
    //     + color2
    //     + ")";
    body.style.background =
        "radial-gradient("
        + color1
        + ", "
        + color2
        + ")";
    console.log(grad);
    css.textContent = document.body.style.background + ";";
    // document.body.style.backgroundColor = grad;

}


window.onload = function () {
    setInterval(change, 5000);
};

function setMode() {
    let request = new XMLHttpRequest();
    request.open(('GET'), "https://127.0.0.1:8000/configuration/change");
    request.send();
    request.onload = () => {
        console.log(request);
        if (request.status === 200) {
            mode = JSON.parse(request.response)['mode'];
            console.log(mode);
        } else {
            console.log('error ${request.status} ${request.statusText}');
        }
    }
}