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

    initInfo() {
        fetch('/get-info/party/', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },

        }).then(response => {
            return response.json();
        }).then(data => {
            this.code = data[0];
            this.id = data[1];
            this.username = data[2];
        })
    }

    connect() {
        this.initInfo();

        this.game = document.querySelector('.game');
        this.start = document.querySelector('.start');

        this.infoAnnounce = document.querySelector('.info-announce');

        this.button = document.querySelector('.points-button');
        this.infoStarting = document.querySelector('.info-starting');
        this.waiting = document.querySelector('.waiting');

        this.chooseType = '';
        this.choosePoint = 0;


        this.isClicked = false;
        this.taskInterval;

        this.atoutButtons = document.querySelectorAll('.atout-button');
        this.pointsButtons = document.querySelectorAll('.points-button');
        this.btnSender = document.querySelector('.btn-sender');
        this.interval = setInterval(this.moveCards, 600);

        this.pointsSelection = document.querySelectorAll('.points-selection');
        this.announce = document.querySelector('.announce');
        this.infoAnnounce = document.querySelector('.info-announce');
        this.announcePlayer = document.querySelector('.announce-player');

        this.cards = document.querySelectorAll('.cardPlayable');
        this.initCardEffect();
        this.initEvent();
    }


    initCardEffect() {


        this.cards.forEach((card) => {
            card.addEventListener('mousemove', (e) => {
                const cardRect = card.getBoundingClientRect(); // Dimensions de la carte
                const centerX = cardRect.left + cardRect.width / 2;
                const centerY = cardRect.top + cardRect.height / 2;

                // Calcul de l'angle en fonction de la position de la souris
                const deltaX = e.clientX - centerX;
                const deltaY = e.clientY - centerY;

                // Augmenter la sensibilité pour des rotations plus fortes
                const rotateX = deltaY / 3; // Plus sensible
                const rotateY = -deltaX / 3;

                // Applique la transformation
                card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });

            // Remet la carte à sa position initiale lorsque la souris quitte
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'rotateX(0deg) rotateY(0deg)';
            });
        });

    }

    initEvent() {
        this.topicUrl = encodeURIComponent('https://example.com/NewChooser-' + this.code);
        this.eventSource = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl}`);


        // Écouter les messages envoyés par Mercure
        this.eventSource.onmessage = (ev) => {
            this.btnSender.click();
            this.btnSender.click();
            this.btnSender.click();

            fetch('/game/choose-atout/' + this.code, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nextPlayer: ev.data,
                })
            }).then(response => {
                return response.json();
            }).then(data => {

                this.btnSender.click();

                if (data.message === "your turn") {
                    this.infoStarting.style.display = 'block';
                    this.waiting.style.display = 'none';
                } else {
                    this.waiting.style.display = 'block';
                }


            });
        };


        this.topicUrl2 = encodeURIComponent('https://example.com/startRound-' + this.code);
        this.eventSource2 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl2}`);


        this.eventSource2.onmessage = (ev) => {

            const namePlayer = document.querySelectorAll('.name');


            this.btnSender.click();
            this.btnSender.click();


            this.infoStarting.style.display = 'none';
            this.waiting.style.display = 'none';

            let nameEvent = event.data;
            let result = nameEvent.replace(/"/g, '');

            namePlayer.forEach((name) => {
                let progressCircle = name.parentElement.children[0].children[0];
                if (name.textContent === result) {
                    progressCircle.style.visibility = 'visible';
                    this.timerPlay(progressCircle.children[0].children[1], result);
                } else {
                    progressCircle.style.visibility = 'hidden';
                }
            });

            if (result === this.username) {

                fetch("/game/checkAnnounce/" + this.code, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                }).then(response => {
                    return response.json();
                }).then(data => {
                    let announce = JSON.parse(data);
                    if (announce !== "vide") {
                        this.infoAnnounce.style.display = 'block';
                    }
                });
                fetch('/game/possibleCard/' + this.code, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        player: result
                    })
                }).then(response => {
                    return response.json();
                }).then(data => {
                    let cardsPossible = JSON.parse(data);
                    cardsPossible.forEach((card) => {
                        this.cards.forEach((card2) => {
                            let cardSlot = card2.slot;
                            let typeCard = cardSlot.split('-')[1];
                            let numberCard = cardSlot.split('-')[0];
                            card2.style.transform = '';

                            if (typeCard === card.type && parseInt(numberCard) === card.number) {
                                card2.style.scale = '1.1';
                                card2.dataset.href = "/game/playCard/" + this.code;
                            }

                        })
                    })
                });
            }


        };


        this.topicUrl3 = encodeURIComponent('https://example.com/PlayCard-' + this.code);
        this.eventSource3 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl3}`);
        this.eventSource3.onmessage = (ev) => {

            const allCards = document.querySelectorAll('.cardPlayable');
            const cards = document.querySelectorAll('.bottom .content-card .card');
            const infoAnnounce = document.querySelector('.info-announce');

            const namePlayer = document.querySelectorAll('.name');
            let data = JSON.parse(event.data);

            allCards.forEach((card) => {
                card.style.scale = '1';
                card.dataset.href = '';
            });

            if (data[2] !== this.username) {
                let cardPlayed;
                allCards.forEach((card) => {
                    if (card.slot === data[1][1] + '-' + data[1][0]) {
                        cardPlayed = card;
                    }
                });

                this.transitionCardPlayedOther(cardPlayed);
            } else {
                this.btnSender.click();
                this.btnSender.click();
            }


            let nameEvent = data[0];
            let result = nameEvent.replace(/"/g, '');
            namePlayer.forEach((name) => {
                let progressCircle = name.parentElement.children[0].children[0];
                if (name.textContent === result) {
                    this.timerPlay(progressCircle.children[0].children[1], result);
                    progressCircle.style.visibility = 'visible';
                } else {
                    progressCircle.style.visibility = 'hidden';
                }
            });
            if (result === this.username) {

                fetch("/game/checkAnnounce/" + this.code, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                }).then(response => {
                    return response.json();
                }).then(data => {
                    let announce = JSON.parse(data);
                    if (announce !== "vide") {
                        infoAnnounce.style.display = 'block';
                    }
                });

                fetch('/game/possibleCard/' + this.code, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        player: result
                    })
                }).then(response => {
                    return response.json();
                }).then(data => {
                    let cardsPossible = JSON.parse(data);
                    cardsPossible.forEach((card) => {
                        cards.forEach((card2) => {
                            let cardSlot = card2.slot;
                            let typeCard = cardSlot.split('-')[1];
                            let numberCard = cardSlot.split('-')[0];
                            if (typeCard === card.type && parseInt(numberCard) === card.number) {
                                card2.style.scale = '1.1';
                                card2.dataset.href = "/game/playCard/" + this.code;

                            }

                        })
                    })
                });
            } else {
                this.infoAnnounce.style.display = 'none';
            }

        }


        this.topicUrl4 = encodeURIComponent('https://example.com/EndRound-' + this.code);
        this.eventSource4 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl4}`);

        this.eventSource4.onmessage = (ev) => {
            this.btnSender.click();
            this.btnSender.click();
            const allCards = document.querySelectorAll('.cardPlayable');
            let data = JSON.parse(event.data);
            if (data[1] !== this.username) {
                let cardPlayed;
                allCards.forEach((card) => {
                    if (card.slot === data[2][1] + '-' + data[2][0]) {
                        cardPlayed = card;
                    }
                });


                this.transitionCardPlayedOther(cardPlayed);
            } else {
                this.btnSender.click();
                this.btnSender.click();
            }


            setTimeout(() => {

                fetch('/game/endHeapOther/' + this.code, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },

                }).then(response => {
                    return response.json();
                }).then(dataFetch => {
                    const names = document.querySelectorAll('.name');
                    let result = dataFetch.replace(/"/g, '');
                    names.forEach((nameComponent) => {
                        if (nameComponent.textContent === result) {
                            this.transitionEndHeap(nameComponent);
                        }
                    });

                });

                let nameEvent = data[0];
                let result = nameEvent.replace(/"/g, '');


                setTimeout(() => {
                    allCards.forEach((card) => {
                        if (result === this.username) {
                            fetch('/game/endHeap/' + this.code, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                            }).then(response => {
                                return response.json();
                            }).then(data => {
                            });
                        }
                        this.btnSender.click();
                        this.btnSender.click();
                        card.style.display = 'block';
                        card.style.scale = '1';
                        card.style.transform = "";
                        card.style.translate = "";
                    });
                }, 300);
            }, 2000);
        }


        this.topicUrl5 = encodeURIComponent('https://example.com/finishedRound-' + this.code);
        this.eventSource5 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl5}`);

        this.eventSource5.onmessage = (ev) => {
            this.btnSender.click();
            this.btnSender.click();
            setTimeout(() => {
                this.btnSender.click();
                this.btnSender.click();
                let nameEvent = event.data;
                let result = nameEvent.replace(/"/g, '');
                if (result === this.username) {
                    fetch('/game/endRound/' + this.code, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            nextPlayer: ev.data,
                        })
                    }).then(response => {
                        return response.json();
                    }).then(data => {

                        this.btnSender.click();

                        if (data.message === "your turn") {
                            this.infoStarting.style.display = 'block';
                            this.waiting.style.display = 'none';
                        } else {
                            this.waiting.style.display = 'block';
                        }


                    });
                }
                this.btnSender.click();
                this.btnSender.click();

            }, 2000);
        }

        this.topicUrl6 = encodeURIComponent('https://example.com/announce-' + this.code);
        this.eventSource6 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl6}`);


        this.eventSource6.onmessage = function (event) {
            this.btnSender.click();
            this.btnSender.click();
        }

        this.topicUrl7 = encodeURIComponent('https://example.com/EndGame-' + this.code);
        this.eventSource7 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl7}`);

        this.eventSource7.onmessage = event => {
            window.location.href = "/game/endGame/" + this.code;
        }

        this.topicUrl8 = encodeURIComponent('https://example.com/LeftGame-' + this.code);
        this.eventSource8 = new EventSource(`http://localhost:32768/.well-known/mercure?topic=${this.topicUrl8}`);

        this.eventSource8.onmessage = (ev) => {
            const alert = document.querySelector('.alertify-notifier');
            let name = event.data;
            name = name.replace(/"/g, '');

            let elt = document.createElement('h3');
            elt.textContent = name + ' a quitté la partie';
            alert.classList.add('ajs-error');

            alert.appendChild(elt)

            alert.appendChild(elt);
            setTimeout(() => {
                alert.removeChild(elt);
            }, 4000);

        }


    }

    clickCard() {

        if (event.target.dataset !== ''){

            event.target.dataset.href = '';

            const numberCard = event.target.slot.split('-')[0];
            const typeCard = event.target.slot.split('-')[1];

            fetch('/game/playCard/'+this.code, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    number: parseInt(numberCard),
                    type: typeCard
                })
            }).then(response => {
                return response.json();
            }).then(data => {
                console.log("CardPlayed")
                this.transitionCardPlayed(event.target);
                this.cards.forEach((card) => {
                    card.dataset.href = '';
                    card.style.scale = '1';
                    card.style.display = 'block';
                });

            });
        }
    }

    transitionCardPlayed(card2) {
        let targetZone = document.querySelector('.middle .bottom-played');

        const rectItem = card2.getBoundingClientRect();
        const rectB = targetZone.getBoundingClientRect();

        /* const deltaX = (rectB.left * 2) - rectItem.left;
         const deltaY = (rectB.top * 2) - rectItem.top;*/

        // Calculer le centre de divB, en tenant compte de `translate(-50%, 0)`
        const divBCenterX = rectB.left + rectB.width / 2; // Centre horizontal de divB
        const divBCenterY = rectB.top + rectB.height / 2; // Centre vertical de divB (inchangé)

        // Calculer la position actuelle de l'élément (centre réel, car translate(-50%, 0) affecte seulement horizontalement)
        const itemCenterX = rectItem.left + rectItem.width / 2;
        const itemCenterY = rectItem.top + rectItem.height / 2;

        const deltaX = divBCenterX - itemCenterX;
        const deltaY = divBCenterY - itemCenterY - 50;


        card2.style.transform = `translate(${deltaX}px, ${deltaY}px)`;


        // Attendre la fin de la transition, puis déplacer dans la divB
        card2.addEventListener(
            'transitionend',
            () => {
                card2.style.display = 'none';
                this.btnSender.click();
                this.btnSender.click();
                this.isClicked = true;
                this.cards.forEach((card3) => {
                    card3.style.scale = '1';
                });
            },
            {once: true} // Pour s'assurer que cet événement n'est exécuté qu'une seule fois
        );
    }

    moveCards() {

        const bottomZone = document.querySelector('.bottom .content-card');
        const topZone = document.querySelector('.top .content-card');
        const rightZone = document.querySelector('.right .content-card');
        const leftZone = document.querySelector('.left .content-card');

        let currentZoneIndex = 0;
        let currentRound = 0;

        const zones = [bottomZone, rightZone, topZone, leftZone];

        if (currentRound >= 12) {
            clearInterval(this.interval);
            this.start.style.display = 'none';
            this.game.style.display = 'block';
            this.btnSender.click();
            this.btnSender.click();
            return;
        }
        let cards = document.querySelector('.heap');
        // Récupération des 3 premières cartes de "middle"

        const targetZone = zones[currentZoneIndex];

        let cardsToMove;
        if (currentRound >= 8) {
            cardsToMove = Array.from(cards.children).slice(0, 2);
        } else {
            cardsToMove = Array.from(cards.children).slice(0, 3);
        }
        cardsToMove.forEach((card) => {
            document.body.appendChild(card);
            getComputedStyle(card).top;

            let left, top, angle;
            if (currentZoneIndex === 0) {
                left = 50;
                top = 75;
            } else if (currentZoneIndex === 1) {
                left = 85;
                top = 50;
                angle = 90;
            } else if (currentZoneIndex === 2) {
                left = 50;
                top = 25;
            } else if (currentZoneIndex === 3) {
                left = 15;
                top = 50;
                angle = 90;
            }
            card.style.left = `${left}%`;
            card.style.top = `${top}%`;
            card.style.transform = `rotate(${angle}deg)`;
            const currentIndex = currentZoneIndex;
            console.log(currentZoneIndex);
            card.addEventListener('transitionend', function onTransitionEnd() {

                card.style.position = 'relative';
                card.style.left = '';
                card.style.top = '';
                card.classList.remove('card-heap0', 'card-heap1', 'card-heap2');
                card.classList.add('cardPlayable');
                if (currentIndex === 0) {
                    card.firstElementChild.src = card.title;
                } else if (currentIndex === 1 || currentIndex === 3) {
                    card.style.transform = 'rotate(0deg)';
                    card.firstElementChild.classList.remove('card-image');
                    card.firstElementChild.classList.add('card-image-other');
                }
                targetZone.appendChild(card);
                card.removeEventListener('transitionend', onTransitionEnd);
            });
        });

        if (currentZoneIndex === 3) {
            currentZoneIndex = 0;
        } else {
            currentZoneIndex++;
        }

        currentRound++;
    }

    hideAllTimer() {
        const progressCircle = document.querySelectorAll('#progressCircle');
        progressCircle.forEach((circle) => {
            circle.style.visibility = 'hidden';
        });
    }

    timerPlay(progressCircle, result) {

        if (this.taskInterval) {
            clearInterval(this.taskInterval);
        }

        const duration = 10;
        let elapsed = 0;

        progressCircle.style.visibility = 'visible';
        progressCircle.style.opacity = 1;
        progressCircle.display = 'block';

        const circleLength = 94.2607192993164

        this.isClicked = false;
        progressCircle.style.strokeDashoffset = 94;


        this.taskInterval = setInterval(() => {
            elapsed += 0.1; // Incrémente de 0,1 seconde
            const progress = elapsed / duration; // Calcul du pourcentage de progression
            const cards = document.querySelectorAll('.bottom .content-card .card');

            // Calcule le décalage du cercle
            progressCircle.style.strokeDashoffset = circleLength * (1 - progress);

            // Affiche le temps restant

            // Arrête le timer lorsque le temps est écoulé
            if (elapsed >= duration) {
                progressCircle.style.strokeDashoffset = 94;

                if (this.isClicked === false && result === this.username) {
                    fetch('/game/possibleCard/' + this.code, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            player: result
                        })
                    }).then(response => {
                        return response.json();
                    }).then(data => {
                        let cardsPossible = JSON.parse(data);
                        fetch('/game/playCard/' + this.code, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                number: cardsPossible[0].number,
                                type: cardsPossible[0].type
                            })
                        }).then(response => {
                            return response.json();
                        }).then(data => {
                            this.cards.forEach((card) => {
                                if (card.slot === cardsPossible[0].number + '-' + cardsPossible[0].type) {
                                    this.transitionCardPlayed(card);
                                }
                            });
                            this.cards.forEach((card3) => {
                                card3.style.scale = '1';
                            });
                        });
                    });
                }
                clearInterval(this.taskInterval);

            }
        }, 100);
    }

    transitionCardPlayedOther(card2) {

        let targetZone;
        let val = 0;
        let targetClass = card2.parentElement.parentElement.classList[0];
        if (targetClass === 'right') {
            targetZone = document.querySelector('.middle .right-played');
        } else if (targetClass === 'top') {
            val = 50;
            targetZone = document.querySelector('.middle .top-played');
        } else if (targetClass === 'left') {
            targetZone = document.querySelector('.middle .left-played');
        }

        const rectItem = card2.getBoundingClientRect();
        const rectB = targetZone.getBoundingClientRect();

        /* const deltaX = (rectB.left * 2) - rectItem.left;
         const deltaY = (rectB.top * 2) - rectItem.top;*/

        // Calculer le centre de divB, en tenant compte de `translate(-50%, 0)`
        const divBCenterX = rectB.left + rectB.width / 2; // Centre horizontal de divB
        const divBCenterY = rectB.top + rectB.height / 2; // Centre vertical de divB (inchangé)

        // Calculer la position actuelle de l'élément (centre réel, car translate(-50%, 0) affecte seulement horizontalement)
        const itemCenterX = rectItem.left + rectItem.width / 2;
        const itemCenterY = rectItem.top + rectItem.height / 2;

        const deltaX = divBCenterX - itemCenterX;
        const deltaY = divBCenterY - itemCenterY + val;

        card2.style.transform = `translate(${deltaX}px, ${deltaY}px)`;

        // Attendre la fin de la transition, puis déplacer dans la divB
        card2.addEventListener(
            'transitionend',
            () => {
                card2.style.display = 'none';
                this.btnSender.click();
                this.btnSender.click();
                this.btnSender.click();
                this.isClicked = false;
            },
            {once: true}
        )// Pour s'assurer que cet événement n'est exécut
    }


    transitionEndHeap(nameComponent) {
        const cardsPlayed = document.querySelectorAll('.card-heap-played');

        cardsPlayed.forEach((card) => {

                document.body.appendChild(card);
                getComputedStyle(card).top;


                card.style.position = 'absolute';
                card.style.transition = 'transform 0.5s';
                card.style.left = `50%`;
                card.style.top = `50%`;
                card.style.transform = `translate(-50%, -50%)`;
                card.style.rotate = '0deg';

                /*card.style.transition = '0.5s';
                card.style.transform = `translate(500px, 800px)`;*/


                card.addEventListener(
                    'transitionend',
                    () => {
                        card.style.display = 'none';
                        this.btnSender.click();
                        this.btnSender.click();
                    }, {once: true}
                );

            },
        );

    }

    passAnnounce() {
        this.infoAnnounce.style.display = 'none';
    }

    validAnnounce() {
        fetch('/game/announce/' + this.code, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        }).then(response => {
            return response.json();
        }).then(data => {
            console.log("announce")
            this.infoAnnounce.style.display = 'none';
            this.btnSender.click();
            this.btnSender.click();

        });
    }


    passButton() {
        fetch('/game/nextChooser/' + this.code, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                player: this.username,
                choosePoint: null,
                chooseType: null
            })
        }).then(response => {
            return response.json();
        }).then(data => {
            this.infoStarting.style.display = 'none';
            this.btnSender.click();
            this.btnSender.click();

        });
        this.atoutButtons.forEach(button => {
            this.button.style.backgroundColor = "#007bff";
        });
        this.pointsButtons.forEach(button => {
            this.button.style.backgroundColor = "#007bff";
        });
    }

    validButton() {

        if (this.choosePoint !== 0 && this.chooseType !== '') {
            fetch('/game/nextChooser/' + this.code, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    player: this.username,
                    choosePoint: this.choosePoint,
                    chooseType: this.chooseType
                })
            }).then(response => {
                return response.json();
            }).then(data => {
                console.log("nextChooser")
                this.infoStarting.style.display = 'none';
                this.btnSender.click();
                this.btnSender.click();


            });
        } else {
            console.log("impossible")

        }


    }

    clickAtoutButton() {
        this.atoutButtons.forEach(button => {
            button.style.backgroundColor = "#007bff";
        });
        this.chooseType = event.target.dataset.type;
        event.target.style.backgroundColor = "navy";
    }

    clickPointsButton() {
        this.pointsButtons.forEach(button => {
            button.style.backgroundColor = "#007bff";
        });
        this.choosePoint = event.target.dataset.points;
        event.target.style.backgroundColor = "navy";
    }

}
