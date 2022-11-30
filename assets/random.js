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


var Unsplash = require('unsplash-js');
var nodeFetch = require('node-fetch');

const unsplash = Unsplash.createApi({
    accessKey: token,
    fetch: nodeFetch,
});

var random;
unsplash.photos.getRandom({
    count: 10,
    // orientation: 'portrait'
    orientation: 'landscape'
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
        ;
        return random;
    }
});

console.log(random);

function change() {
    console.log("Change");
}

window.onload = function () {
    setInterval(change, 5000);
};
