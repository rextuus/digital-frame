/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/stage.css';

// start the Stimulus application
import './bootstrap';
var mode;

// var Unsplash = require('unsplash-js');
// var Spotify = require('spotify-web-api-js');
// var nodeFetch = require('node-fetch');
//
// const unsplash = Unsplash.createApi({
//     accessKey: 'IggqUsh5jKqF7WtHOiX64x8BYrLSfC86SyrmySDaWFY',
//     // accessKey: 'https://mywebsite.com/unsplash-proxy',
//     fetch: nodeFetch,
// });
//
//
//
// // set token to spotify api
// var spotify = new Spotify();
// spotify.setAccessToken(token);
//
// var mode = 0;
//
// var photo = unsplash.photos.get(
//     { photoId: 'pFqrYbhIAXs' }
// ).then(result => {
//     if (result.errors) {
//         // handle error here
//         console.log('error occurred: ', result.errors[0]);
//     } else {
//         // handle success here
//         photo = result.response;
//         console.log(photo);
//         return photo;
//     }
// });
// console.log(photo);
//
// function change() {
//     let request = new XMLHttpRequest();
//     request.open(('GET'), "https://127.0.0.1:8000/stage/change");
//     request.send();
//     request.onload = () => {
//         console.log(request);
//         if (request.status == 200){
//             mode = JSON.parse(request.response)['mode'];
//         }else{
//             console.log('error ${request.status} ${request.statusText}');
//         }
//     }
//
//     if (mode === 0){
//         document.getElementById('random-image').src = photo['urls']['regular'];
//     }
//
//     else{
//         spotify.getMyCurrentPlayingTrack().then(
//             function (data) {
//                 console.log('Search by "Love"', data);
//                 document.getElementById('the-image').src = data['item']['album']['images'][0]['url'];
//                 // document.body.style.backgroundImage = "url('"+data['item']['album']['images'][0]['url']+"')";
//
//
//                 const canvas = document.getElementById('the-canvas')
//                 var ctx = canvas.getContext('2d');
//                 const image = document.getElementById('the-image')
//                 // const imageSrc = document.getElementById('image-src')
//                 // const DEFAULT_IMAGE_SRC = "https://images.unsplash.com/photo-1519515533456-ed9f2c73ca33?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
//
//                 image.addEventListener('load', e => {
//                     ctx.drawImage(image, 0, 0, 1, 1);
//                 });
//             },
//             function (err) {
//                 console.error(err);
//             }
//         );
//     }
// }
//
//
//
// window.onload = function () {
//     setInterval(change, 5000);
// };

function setMode(){
    let request = new XMLHttpRequest();
    request.open(('GET'), "https://127.0.0.1:8000/stage/change");
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