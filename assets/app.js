/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import $ from 'jquery';
// Uncomment to support legacy code:
//global.$ = $;
import 'bootstrap'; // adds functions to jQuery
// import '@fortawesome/fontawesome-free/js/all';

window.onbeforeunload = function () {
    $('#loading').show();
};
$(window).ready(function () {
    $('#loading').hide();
});
