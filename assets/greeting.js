import './styles/greeting.css';
import './bootstrap';

const greetingMode = 3;
let currentMode = 3;

function change() {
    setMode();
    if (currentMode === greetingMode) {

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
            calculateImagePos();
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
