/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/config.scss';

import './bootstrap.js';
// start the Stimulus application
import * as bootstrap from 'bootstrap';

if (bootstrap) {
    console.log('Bootstrap is loaded successfully:', bootstrap);
} else {
    console.log('Bootstrap is NOT loaded.');
}

activeButton = document.getElementById(activeButton);
console.log(activeButton);
activeButton.classList.remove('btn-disabled');
activeButton.classList.add('btn-enabled');

let nextButton = document.getElementById('configuration_next');
let tagButton = document.getElementById('configuration_tag');
let tagLabel = document.getElementById('tag-label');
let newTagButton = document.getElementById('configuration_newTag');

if (activeButton.id === 'configuration_image'){
}

if (activeButton.id === 'configuration_artsy'){
    document.getElementById('configuration_next'). style.display = 'block';
    tagButton.style.display = 'none';
    tagLabel.style.display = 'none';
    newTagButton.style.display = 'none';
}

if (activeButton.id === 'configuration_spotify'){
    console.log('spotify');
    nextButton.style.display = 'none';
    tagButton.style.display = 'none';
    tagLabel.style.display = 'none';
    newTagButton.style.display = 'none';
}

if (activeButton.id === 'configuration_greeting'){
    console.log('spotify');
    nextButton.style.display = 'none';
    tagButton.style.display = 'none';
    tagLabel.style.display = 'none';
    newTagButton.style.display = 'none';
}
