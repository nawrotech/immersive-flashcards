import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {

    static targets = ['prevButton', 'nextButton', 'flashcardContainer', 'flashcard'];

    static values = {
        numOfCards: Number,
        storePracticeResultsUrl: String
    };

    results = [];
    counter = 0;

    connect() {
        console.log(this.storePracticeResultsUrlValue);
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

        const existingIndex = this.results.findIndex(result => result.id === id);
        if (existingIndex !== -1) {
          this.results[existingIndex] = flashcardScore;
        } else {
          this.results.push(flashcardScore);
        }

        const lastFlashcardId = this.flashcardTargets.at(-1).dataset.flashcardId;
        if (lastFlashcardId == id && this.results.find(flashcard => flashcard.id == lastFlashcardId)) {
            this.sendResults();
        }
    }

    sendResults() {
        fetch(this.storePracticeResultsUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(this.results,
            )
        })
        .then(response => response.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.url;
            }
        })
        .catch(error => console.error('Error fetching images:', error));
        ;
    }

    correct(e) {    
        const practiceFlashcard = e.currentTarget.closest('.practice-flashcard');
        const flashcardId = practiceFlashcard.dataset.flashcardId;
        this.storeResult(flashcardId, true);

    }

    incorrect(e) {
        const practiceFlashcard = e.currentTarget.closest('.practice-flashcard');
        const flashcardId = practiceFlashcard.dataset.flashcardId;
        this.storeResult(flashcardId, false);
    }


}
