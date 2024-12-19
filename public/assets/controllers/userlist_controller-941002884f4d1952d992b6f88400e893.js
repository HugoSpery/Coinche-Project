import {Controller} from '@hotwired/stimulus';

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
        this.btnResetUsers = document.querySelector(".btn-reset-user");
    }

    visit() {
        event.target.dataset.href = "test";
        alert(event.target.dataset.href);
        alert("laa");
        if (event.target.nodeName !== "BUTTON") {
            window.location.href = event.target.dataset.href;
        }
    }

    add(event) {
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
                if (event && event.target.slot === "reset") {
                    window.location.reload();
                } else {
                    this.btnResetUsers.click();
                }
            });




    }

    remove() {

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
                if (event && event.target.slot === "reset") {
                    window.location.reload();
                } else {
                    if(this.btnResetUsers){
                        this.btnResetUsers.click();
                    }else{
                        window.location.reload();
                    }

                }
            });

    }
}
