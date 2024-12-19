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
        this.messageContainer.scrollTo({ top: this.messageContainer.scrollHeight, behavior: 'smooth' });
    }

    connect() {
        Turbo.cache.clear();
        this.btnSender = document.querySelector(".btn-sender");
        this.messageContainer = document.querySelector('.messages');
        this.scrollToBottom();
        this.initMercureSubscriptions();
    }


    send(){
        this.sendMessage(event);
    }

    sendMessage(event){
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


    resetComponent(){
        this.btnSender = document.querySelector(".btn-sender");
        if (this.btnSender) {
            this.btnSender.click();
            this.btnSender.click();
        }
    }

    kickedPlayerHandle(idPlayer,event){
        let data = JSON.parse(event.data);
        if (data.toString() === idPlayer.toString()) {
            window.location.href = '/';
        }
    }

    resetMessage(){
        this.btnResetChatComponent.click();
        this.btnResetChatComponent.click();

        setTimeout(() => {
            this.scrollToBottom();
            this.btnResetChatComponent.click();
        }, 200);
    }

    launchPartyHandler(partyCode){
        window.location.href = '/party/' + partyCode;
    }
    initMercureSubscriptions() {

        this.btnResetChatComponent = document.querySelector(".btn-chat");
        this.messageContainer = document.querySelector('.messages');

        const topicUrl = encodeURIComponent('https://example.com/NewPlayer');
        const eventSource = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${topicUrl}`);

        this.partyCode = document.querySelector('.code').slot;
        this.idPlayer = document.querySelector('.code').id;

        const chooseTpe = document.querySelector(".choose-type");

        if (chooseTpe) {
            chooseTpe.addEventListener("change", () => {
                const input = chooseTpe.value;
                console.log(input);
                fetch('/update/party/'+this.partyCode, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({input: input})
                }).then((response) => {
                    return response.json();
                }).then((data) => {
                    console.log(data)
                });

            })
        }

        const topicLaunch = encodeURIComponent('https://example.com/LaunchParty');
        const eventLaunch = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${topicLaunch}`);

        // Écouter les messages envoyés par Mercure
        eventLaunch.onmessage = (event) => {
            window.location.href = '/party/' + this.partyCode;

        };

        console.log("la",this.partyCode);
        const topicNewMessage = encodeURIComponent('https://example.com/NewMessage-'+this.partyCode);
        const eventSourceMessage = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${topicNewMessage}`);



        eventSourceMessage.onmessage = (event) => {
            this.resetMessage();
        };

        const btnReset = document.querySelector('.btn-sender');

        // Écouter les messages envoyés par Mercure
        eventSource.onmessage = (event) => {
            console.log("laa2");
            this.resetComponent();
        };

        const topicUrl3 = encodeURIComponent('https://example.com/KickedPlayer');
        const eventSource3 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${topicUrl3}`);

        eventSource3.onmessage = (event) => {
            this.kickedPlayerHandle(this.idPlayer,event);
        };






        function leftPlayerHandler() {
            fetch('/leftLobby/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify
            }).then((response) => {
                return response.json();
            }).then((data) => {
                console.log(data)
            });
        }


    }




}