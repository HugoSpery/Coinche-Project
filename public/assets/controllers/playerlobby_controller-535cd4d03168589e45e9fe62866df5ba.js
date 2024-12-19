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




    scrollToBottom() {
        this.messageContainer = document.querySelector('.messages');
        this.messageContainer.scrollTo({ top: this.messageContainer.scrollHeight, behavior: 'smooth' });
    }

    connect() {
        this.btnSender = document.querySelector(".btn-sender");
        this.scrollToBottom();
    }

    send(){
        alert(event.target.dataset.href);
        this.sendMessage(event);
    }

    sendMessage(event){
        alert(event.target.dataset.href);
        this.input = document.querySelector(".send-message");
        this.btnResetChat = document.querySelector(".btn-chat");
        fetch(event.target.dataset.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "message": this.input.value
            })
        })
            .then((response) => response.json())
            .then((data) => {
                this.input.value = "";
                this.btnResetChat.click();
                this.btnResetChat.click();
                setTimeout(() => {
                    this.scrollToBottom();
                }, 100);
            });

    }

    detectEnter(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            this.sendMessage(event);
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
