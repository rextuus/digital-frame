/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/stage.scss';

// start the Stimulus application
import './bootstrap';
var mode;

console.log(document.getElementById(activeButton));
document.getElementById(activeButton).classList.remove('btn-disabled');
document.getElementById(activeButton).classList.add('btn-enabled');

document.getElementById('configuration_next'). style.display = 'none';
document.getElementById('configuration_tag'). style.display = 'none';
if (activeButton === 'configuration_image'){
    document.getElementById('configuration_next'). style.display = 'block';
    document.getElementById('configuration_tag'). style.display = 'block';
}

if (activeButton === 'configuration_artsy'){
    document.getElementById('configuration_next'). style.display = 'block';
}

function setMode(){
    let request = new XMLHttpRequest();
    request.open(('GET'), "http://127.0.0.1:8000/stage/change");
    request.send();
    request.onload = () => {
        console.log(request);
        if (request.status === 200){
            mode = JSON.parse(request.response)['mode'];
        }else{
            console.log('error ${request.status} ${request.statusText}');
        }
    }
}