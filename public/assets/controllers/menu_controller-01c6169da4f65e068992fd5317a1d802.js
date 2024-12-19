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
        this.url = new URL(window.location);
        this.openParam = document.querySelector(".parameter");
        this.paramMenu = document.querySelector(".menu");
        this.closeMenu = document.querySelector(".menu-close");
        this.contentFriend = document.querySelector(".content-friend");
        this.buttonClose = document.querySelector(".close-friends");
        this.listFriends = document.querySelector(".list-friends");
        this.openFriends = document.querySelector(".open-friends");
        this.openParam.addEventListener("click", () => {
            this.paramMenu.classList.add("open");
            this.openParam.classList.add("open");
            this.paramMenu.addEventListener("transitionend", () => {
                if (this.paramMenu.style.visibility === "hidden") {
                    window.history.replaceState({}, "", this.url);
                } else {
                    this.paramMenu.style.display = "block";

                }

            });
            this.contentFriend.style.display = "block";
            this.url.searchParams.set("menu", "open");
            window.history.replaceState({}, "", this.url);
        });

        this.closeMenu.addEventListener("click", () => {
            this.paramMenu.classList.remove("open");
            this.openParam.classList.remove("open");
            this.url.searchParams.set("menu", "");

            window.history.replaceState({}, "", this.url);
        })
        if(this.openFriends) {
            this.openFriends.addEventListener("click", () => {
                this.listFriends.classList.add("openFriends");
                this.url.searchParams.set("friend", "open");
                window.history.replaceState({}, "", this.url);
            })
        }

        this.buttonClose.addEventListener("click", () => {
            this.listFriends.classList.remove("openFriends");
            this.url.searchParams.set("friend", "");
            window.history.replaceState({}, "", this.url);
        })
    }

}
