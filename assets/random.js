import './styles/random.scss';
import './bootstrap';

const imageMode = 1;
let currentMode = 1;
let next = false;
let nextCount = 0;


var Unsplash = require('unsplash-js');
var nodeFetch = require('node-fetch');

const unsplash = Unsplash.createApi({
    accessKey: token,
    fetch: nodeFetch,
});

var random;
randomImage();

function randomImageOld(){
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
            calculateImagePos();
            return random;
        }
    });
}

function randomImage(){
    let request = new XMLHttpRequest();
    request.open(('GET'), "http://127.0.0.1:8000/image/random");
    request.send();
    request.onload = () => {
        if (request.status === 200) {
            let response = JSON.parse(request.response);
            document.getElementById('random-image').src = response['url'];
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
            randomImage();
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
    // image.style.marginTop = (total/2) + "px";
}