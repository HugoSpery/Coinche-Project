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
        this.btnSender = document.querySelector(".btn-sender");
        this.btnResetChat = document.querySelector(".btn-chat");
    }

    detectEnter(event) {
        this.input = document.querySelector(".send-message");
        if (event.key === 'Enter') {
            event.preventDefault();
            alert("Enter");

        }
    }

    upgrade() {
        this.btnSender = document.querySelector(".btn-sender");
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id,
                "code": event.target.name
            })
        })
            .then((response) => response.json())
            .then((data) => {
                this.btnSender.click();
                this.btnSender.click();
                this.btnSender.click();
            });
    }

    kick() {
        this.btnSender = document.querySelector(".btn-sender");
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id,
                "code": event.target.name
            })
        })
            .then((response) => response.json())
            .then((data) => {
                this.btnSender.click();
                this.btnSender.click();

            });
    }

    update() {
        this.btnSender = document.querySelector(".btn-sender");
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id,
            })
        })
            .then((response) => response.json())
            .then((data) => {
                this.btnSender.click();
                this.btnSender.click();
                if (data.toString() === "start"){
                    window.location.href = "/launch/party/";
                }
            });
    }

    changeTeam(){
        this.btnSender = document.querySelector(".btn-sender");
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id,
            })
        })
            .then((response) => response.json())
            .then((data) => {
                this.btnSender.click();
                this.btnSender.click();
            });
    }


}
