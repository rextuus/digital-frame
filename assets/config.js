import './styles/config.scss';
import './bootstrap.js';
// start the Stimulus application

activeButton = document.getElementById(activeButton);
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
