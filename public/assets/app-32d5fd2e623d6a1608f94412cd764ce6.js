import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *c
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';


const openParam = document.querySelector(".parameter");

const paramMenu = document.querySelector(".menu");

const closeMenu = document.querySelector(".menu-close");
const seeRequests = document.querySelector(".see-requests");
const btnResetFriends = document.querySelector(".btn-reset-friend");
const btnResetRequest = document.querySelector(".btn-reset-friend-request");

const url = new URL(window.location);
const listFriends = document.querySelector(".list-friends");
const contentFriend = document.querySelector(".content-friend");
const friends = document.querySelector(".friends");
const addFriends = document.querySelector(".add-friends");

const optionsFriend = document.querySelectorAll(".option-friends");
const addFriend = document.querySelector(".add-friend");
const seeFriend = document.querySelector(".see-friend");

/*if (url.searchParams.get("menu") === "open") {
    paramMenu.classList.add("open");
    openParam.classList.add("open");
    paramMenu.style.display = "block";
}
if (url.searchParams.get("friend") === "open") {
    listFriends.classList.add("openFriends");
}
if(url.searchParams.get("addFriend") === "open" && url.searchParams.get("friend") === "open"){
    friends.style.display = "none";
    addFriends.style.display = "block";
    optionsFriend.forEach(option => {
        option.classList.remove("active")
    });
    addFriend.classList.add("active");
}*/

if(openParam!=null){
    openParam.addEventListener("click", () => {

        paramMenu.classList.add("open");
        openParam.classList.add("open");
        paramMenu.addEventListener("transitionend", () => {
            if (paramMenu.style.visibility==="hidden") {
                window.history.replaceState({}, "", url);
            } else {
                paramMenu.style.display = "block";

            }

        });
        contentFriend.style.display = "block";
        url.searchParams.set("menu", "open");
        window.history.replaceState({}, "", url);
    });

    closeMenu.addEventListener("click", () => {
        paramMenu.classList.toggle("open");
        openParam.classList.toggle("open");
        contentFriend.style.display = "none";
        url.searchParams.set("menu", "");

        window.history.replaceState({}, "", url);
    })
}





if(addFriend){
    addFriend.addEventListener("click", () => {
        optionsFriend.forEach(option => {
            option.classList.remove("active")
        });
        addFriend.classList.add("active");


        url.searchParams.set("addFriend", "open");

        window.history.replaceState({}, "", url);
    })

    seeFriend.addEventListener("click", () => {
        optionsFriend.forEach(option => {
            option.classList.remove("active")
        });
        seeFriend.classList.add("active");
        url.searchParams.set("addFriend", "");
        window.history.replaceState({}, "", url);

    })
}



const inputSearchUser = document.querySelector(".search-user");

if (inputSearchUser){
    inputSearchUser.addEventListener("keyup", () => {
        const input = inputSearchUser.value;
        const url = new URL(window.location);

        // Mettre à jour ou ajouter le paramètre `user`
        if (input) {
            url.searchParams.set("user", input); // Met à jour ou ajoute ?user=
        } else {
            url.searchParams.delete("user"); // Supprime le paramètre si le champ est vide
        }

        // Remplacer l'URL dans le navigateur sans recharger la page
        window.history.replaceState({}, "", url);

    })
}


const buttonFriend = document.querySelectorAll(".button-friend");

function sendRequest(event) {
    const button = event.target;
    fetch("/send/request", {
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
            button.textContent = "En Attente";
            button.classList.add("waiting-request")
        });

    button.removeEventListener("click", sendRequest);
    button.addEventListener("click", removeRequest);
}

function removeRequest(event) {
    const button = event.target;
    fetch("/send/remove-request", {
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
            const icon = document.createElement('i');
            button.textContent = "";
            icon.classList.add('fa-solid', 'fa-plus');
            button.appendChild(icon);
            button.appendChild(document.createTextNode(' Ajouter'));
            button.classList.remove("waiting-request")

        });

    button.removeEventListener("click", removeRequest);
    button.addEventListener("click", sendRequest);
}

if(buttonFriend) {


    buttonFriend.forEach(button => {
        if (button.textContent.replace(/\s/g, '') !== "EnAttente") {
            button.addEventListener("click", sendRequest);

        } else {
            button.addEventListener("click", removeRequest);
        }

    })
}

const openFriends = document.querySelector(".open-friends");

if (openFriends){
    openFriends.addEventListener("click", () => {
        listFriends.classList.add("openFriends");
        url.searchParams.set("friend", "open");
        window.history.replaceState({}, "", url);
    })
}


const seeRequest = document.querySelector(".see-request");

if (seeRequest){
    seeRequest.addEventListener("click", () => {
        optionsFriend.forEach(option => {
            option.classList.remove("active")
        });
        seeRequest.classList.add("active");
        url.searchParams.set("addFriend", "");
        window.history.replaceState({}, "", url);

    })
}


const acceptRequest = document.querySelectorAll(".accept-request");
const refuseRequest = document.querySelectorAll(".refuse-request");



const buttonClose = document.querySelector(".close-friends");

if (buttonClose){
    buttonClose.addEventListener("click", () => {
        listFriends.classList.remove("openFriends");
        url.searchParams.set("friend", "");
        window.history.replaceState({}, "", url);
    })
}

const deleteFriend = document.querySelectorAll(".delete-friend");

if(deleteFriend) {
    deleteFriend.forEach(button => {
      button.addEventListener("click", () => {
          fetch("/send/delete-friend", {
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
                  btnResetFriends.click();
              });
      })
    })
}

const inviteParty = document.querySelectorAll(".invite-party");

function sendInvite(event) {
    const button = event.target;
    fetch("/send/invite-party", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            "id": button.id,
            "location": window.location.pathname
        })
    })
        .then((response) => response.json())
        .then((data) => {
            if (data !== "ok"){
                window.location.href="/lobby/"+data;
            }
            btnResetFriends.click();
            btnResetFriends.click();
        });

    button.removeEventListener("click", sendInvite);
    button.addEventListener("click", removeInvite);

}

const removeInviteParty = document.querySelectorAll(".remove-invite");

function removeInvite(event){
    const button = event.target;
    fetch("/send/remove-invite", {
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
            btnResetFriends.click();
        });

    button.removeEventListener("click", removeInvite);
    button.addEventListener("click", sendInvite);
}

if(inviteParty) {
    inviteParty.forEach(button => {
        button.addEventListener("click", sendInvite);
    })
}

if(removeInviteParty) {
    removeInviteParty.forEach(button => {
        button.addEventListener("click", removeInvite);

    })
}

const acceptInvite = document.querySelectorAll(".alertify-notifier");

function acceptInvitation(event){
    const button = event.target;
    fetch("/send/accept-invite", {
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
            window.location.href="/lobby/"+data;
        });

}



if(acceptInvite) {
    acceptInvite.forEach(button => {
        button.addEventListener("click",(event) => {
            if (event.target.classList.contains("accept-invite")){
                acceptInvitation(event);
            }
        })
        //button.addEventListener("click", acceptInvitation);
    })

}



