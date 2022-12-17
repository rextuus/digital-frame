/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/random.css';

// start the Stimulus application
import './bootstrap';

const imageMode = 1;
let currentMode = 1;
let next = false;


var Unsplash = require('unsplash-js');
var nodeFetch = require('node-fetch');

const unsplash = Unsplash.createApi({
    accessKey: token,
    fetch: nodeFetch,
});

var random;
randomImage();

function randomImage(){
    unsplash.photos.getRandom({
        count: 10,
        // orientation: 'portrait'
        orientation: 'portrait'
    }).then(result => {
        if (result.errors) {
            // handle error here
            console.log('error occurred: ', result.errors[0]);
        } else {
            // handle success here
            random = result.response;
            // document.body.style.backgroundImage = "url('"+random[0]['urls']['regular']+"')";
            document.getElementById('random-image').src = random[0]['urls']['regular'];
            console.log(window.screen.height);
            console.log(window.screen.width);
            return random;
        }
    });
}

function change() {
    setMode();

    if (currentMode === imageMode) {
        console.log(next);

        if (next){
            randomImage();
            next = false;
            let request = new XMLHttpRequest();
            request.open(('GET'), "http://127.0.0.1:8000/configuration/next");
            request.send();
        }
    } else {
        window.location.replace("http://127.0.0.1:8000/stage");
    }
}

window.onload = function () {
    setInterval(change, 5000);
};

function setMode() {
    let request = new XMLHttpRequest();
    request.open(('GET'), "http://127.0.0.1:8000/configuration/change");
    request.send();
    request.onload = () => {
        console.log(request);
        if (request.status === 200) {
            currentMode = JSON.parse(request.response)['mode'];
            next = JSON.parse(request.response)['isNext'];
            console.log(currentMode);
        } else {
            console.log('error ${request.status} ${request.statusText}');
        }
    }
}