import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {

    static targets = ['prevButton', 'nextButton', 'flashcardContainer', 'flashcard'];

    static values = {
        numOfCards: Number,
        storePracticeResultsUrl: String
    };

    result = [];
    counter = 0;

    connect() {
        this.hideButtons();
    }

    reverseSides() {
        this.flashcardTargets.forEach(flashcard => flashcard.classList.toggle('inversed')); 
    }


    next() {
        if (this.counter < this.numOfCardsValue - 1) {
            this.counter += 1;
        }
        this.flashcardContainerTarget.style.setProperty("--counter", this.counter);
        this.hideButtons();
    }

    prev() {
        if (this.counter >= 0) {
            this.counter -= 1;
        }
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


    storeResult(id, result) {
        const flashcardScore = {
            id,
            result
        };

        const existingIndex = this.result.findIndex(result => result.id === id);
        if (existingIndex !== -1) {
          this.result[existingIndex] = flashcardScore;
        } else {
          this.result.push(flashcardScore);
        }

        const lastFlashcardId = this.flashcardTargets.at(-1).dataset.flashcardId;
        if (lastFlashcardId == id && this.result.find(flashcard => flashcard.id == lastFlashcardId)) {
            this.sendResult();
        }
    }

    sendResult() {
        fetch(this.storePracticeResultsUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(this.result,
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
        this.storeResult(flashcardId, 'correct');

        // const scoredFlashcard = this.result.find(el => el.id = flashcardId);

        practiceFlashcard.classList.add("correct", "active");
        this.next();

    }

    incorrect(e) {
        const practiceFlashcard = e.currentTarget.closest('.practice-flashcard');
        const flashcardId = practiceFlashcard.dataset.flashcardId;

        

        this.storeResult(flashcardId, 'incorrect');
        practiceFlashcard.classList.add("incorrect", "active");
        this.next();

    }


}
