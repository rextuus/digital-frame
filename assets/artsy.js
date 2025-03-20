/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/artsy.scss';

// start the Stimulus application
import './bootstrap';

const imageMode = 4;
let currentMode = 4;
let next = false;
let nextCount = 0;

var random;
calculateImagePos();

function nextArtwork(){
    let request = new XMLHttpRequest();
    request.open(('GET'), "http://127.0.0.1:8000/artsy/next");
    request.send();
    request.onload = () => {
        if (request.status === 200) {
            let response = JSON.parse(request.response);
            document.getElementById('random-image').src = response['image_url'];
            calculateImagePos();
        } else {
            console.log('error ${request.status} ${request.statusText}');
        }
    }

}


function change() {
    setMode();
    calculateImagePos();
    if (currentMode === imageMode) {
        if (next && nextCount < 1){
            next = false;
            nextCount = nextCount + 1;
            let request = new XMLHttpRequest();
            request.open(('GET'), "http://127.0.0.1:8000/configuration/next");
            request.send();
            nextArtwork();
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
        if (request.status === 200) {
            currentMode = JSON.parse(request.response)['mode'];
            next = JSON.parse(request.response)['isNext'];
            if (next && nextCount === 1){
                next = false;
                nextCount = 0;
            }
        } else {
            console.log('error ${request.status} ${request.statusText}');
        }
    }
}

function calculateImagePos(){
    let image = document.getElementById('random-image');
    let distanceFromTop = image.offsetTop;
    let distanceFromBottom = window.innerHeight - (image.offsetTop + image.offsetHeight);
    let total = distanceFromTop + distanceFromBottom;
    image.style.marginTop = (total/2) + "px";
}