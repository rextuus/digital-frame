/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/greeting.css';

// start the Stimulus application
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
            console.log(currentMode);
        } else {
            console.log('error ${request.status} ${request.statusText}');
        }
    }
}
