import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {

    static targets = ['prevButton', 'nextButton', 'flashcardContainer', 'flashcard'];

    static values = {
        numOfCards: Number
    };

    results = [];
    counter = 0;

    connect() {
        console.log(this.counter, this.numOfCardsValue);
        console.log(this.nextButtonTarget, this.prevButtonTarget);
        this.hideButtons();
    }


    reverseSides() {
        this.flashcardTargets.forEach(flashcard => flashcard.classList.toggle('inversed')); 
    }


    next() {
        this.counter += 1;
        this.flashcardContainerTarget.style.setProperty("--counter", this.counter);
        this.hideButtons();
    }

    prev() {
        this.counter -= 1;
        this.flashcardContainerTarget.style.setProperty("--counter", this.counter);    
        this.hideButtons();
    }

    hideButtons() {
        this.nextButtonTarget.classList.toggle('hidden', this.counter >= this.numOfCardsValue - 1);
        this.prevButtonTarget.classList.toggle('hidden', this.counter <= 0);
    }


    rotate(e) {
        e.currentTarget.closest('.practice-flashcard').classList.toggle('rotated');
        console.log(e.currentTarget.closest('.practice-flashcard'));
    }


    storeResult(id, correct) {
        const flashcardScore = {
            id,
            correct
        };
        this.results.push(obj);
    }

    correct(e) {
        const flashcardScore = this.storeResult(id, true);
    }

    incorrect(e) {
        const flashcardScore = this.storeResult(id, false);
    }
}
