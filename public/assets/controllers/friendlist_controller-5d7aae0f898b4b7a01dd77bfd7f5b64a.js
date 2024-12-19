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
        this.btnResetFriends = document.querySelector(".btn-reset-friend");
        this.btnResetRequest = document.querySelector(".btn-reset-friend-request");
    }

    visit(){
        if (event.target.nodeName !== "BUTTON"){
            window.location.href = event.target.dataset.href;
        }
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
                this.btnResetFriends.click();
                this.btnResetRequest.click();
                this.btnResetRequest.click();
                this.btnResetFriends.click();
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
                this.btnResetRequest.click();
                this.btnResetRequest.click();
            });
    }

    delete(){
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
                this.btnResetFriends.click();
                this.btnResetFriends.click();
            });
        if (event.target.slot === "reset"){
            window.location.reload();
        }
    }

    invite(){
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "id": event.target.id,
                "location": window.location.pathname
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data !== "ok"){
                    window.location.href="/lobby/"+data;
                }
                this.btnResetFriends.click();
                this.btnResetFriends.click();
            });
    }

    acceptInvite(){
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
                window.location.href="/lobby/"+data;

            });
    }

    removeInvite(){
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
                this.btnResetFriends.click();
                this.btnResetFriends.click();
            });
    }
}
