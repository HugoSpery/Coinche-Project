const topicN = encodeURIComponent('https://example.com/NewRequest');
const event = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${topicN}`);
const btnResetFriends = document.querySelector(".btn-reset-friend");
const btnResetRequest = document.querySelector(".btn-reset-friend-request");
let user;
fetch('/get-info-user',{
    method: "POST",
    headers: {
        "Content-Type": "application/json",
    },
})
    .then((response) => response.json())
    .then((data) => {
        user = data;
    });


event.onmessage = event => {
    let data = JSON.parse(event.data);
    data = data.toString();
    console.log(data);
    if ('{{ app.user.id }}' === data) {
        console.log(user);
        const alert = document.querySelector('.alertify-notifier');

        let elt = document.createElement('h3');
        elt.textContent = "Vous avez une nouvelle demande d'ami";
        elt.classList.add('ajs-success');

        alert.appendChild(elt);

        btnResetRequest.click();
        btnResetRequest.click();

        setTimeout(() => {
            alert.removeChild(elt);
        }, 4000);

        const acceptRequest = document.querySelectorAll(".accept-request");
        const refuseRequest = document.querySelectorAll(".refuse-request");
        acceptRequest.forEach(button => {
            button.addEventListener("click", () => {
                fetch("/send/accept-request", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        "id": button.id
                    })
                })
                    .then((response) => response.json())
                    .then((data) => {
                        console.log(data)
                        btnResetFriends.click();
                        btnResetRequest.click();
                        btnResetRequest.click();
                        btnResetFriends.click();
                    });
            })
        })

    }

    // Do something with the data
}

const topic2 = encodeURIComponent('https://example.com/NewPartyRequest');
const event2 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${topic2}`);

event2.onmessage = event => {
    let data = JSON.parse(event.data);

    let id = data[1];
    let name = data[0];
    let nameUser = data[2];
    console.log(data);
    if ('{{ app.user.username }}' === nameUser) {
        console.log("laa");
        const alert = document.querySelector('.alertify-notifier');

        let elt = document.createElement('h3');
        elt.textContent = "Vous avez reçu une invitation de " + name + " ! ";
        alert.classList.add('ajs-success');
        alert.classList.remove('ajs-error');
        let button = document.createElement('button')
        button.classList.add('default-button')
        button.classList.add('accept-invite');
        button.textContent = "Accepter";
        button.id = id;

        alert.appendChild(elt);

        alert.appendChild(button);

        btnResetFriends.click();
        btnResetFriends.click();

        setTimeout(() => {
            alert.removeChild(elt);
            alert.removeChild(button);
            alert.classList.remove('ajs-success');
        }, 12000);

    }
    // Do something with the data
}
