import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *c
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import './home.js';


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
const btnResetUsers = document.querySelector(".btn-reset-user");

console.log("la");

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


const openFriends = document.querySelector(".open-friends");



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







