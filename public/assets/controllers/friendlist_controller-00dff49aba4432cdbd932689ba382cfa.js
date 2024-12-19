import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * friendlist_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {

    }

    accept(event){
        console.log(event.source)
        alert(this.element.getAttribute('data-href'));
    }

    decline(){
        alert("decline")
    }
}
