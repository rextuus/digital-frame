import './styles/stage.scss';
import './bootstrap';

var mode;

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