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

        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id
            })
        })
            .then((response) => response.json())
            .then((data) => {
                btnResetFriends.click();
                btnResetRequest.click();
                btnResetRequest.click();
                btnResetFriends.click();
            });
    }

    decline(){
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id
            })
        })
            .then((response) => response.json())
            .then((data) => {
                console.log(data)
                btnResetRequest.click();
                btnResetRequest.click();
            });
    }
}
