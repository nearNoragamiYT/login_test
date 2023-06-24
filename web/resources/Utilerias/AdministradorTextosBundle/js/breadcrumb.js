/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//Global variables
var table = null;
$(init);
/**
 * @description start all functions that need
 */
function init() {
    $('.dropdown-content a').on('click', function () {
        show_loader_wrapper();
    });
    $('.dropdown-breadcrumb').dropdown({
        inDuration: 300,
        outDuration: 225,
        constrain_width: false, // Does not change width of dropdown to that of the activator
        hover: false, // Activate on hover
        gutter: 0, // Spacing from edge
        belowOrigin: true, // Displays dropdown below the button
        alignment: 'left' // Displays dropdown with edge aligned to the left of button
    });
}
