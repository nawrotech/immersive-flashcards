import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "prevButton",
    "nextButton",
    "flashcardContainer",
    "flashcard",
    "video",
    "rotateButton",
  ];

  static values = {
    numOfCards: Number,
    storePracticeResultsUrl: String,
    slidingTransitionDelay: String,
  };

  result = [];
  counter = 0;

  connect() {
    this.hideButtons();
  }

  next(delay = 0) {
    if (this.counter < this.numOfCardsValue - 1) {
      this.counter += 1;
    }

    this.slide(this.counter, delay);
    this.hideButtons();
  }

  prev(delay = 0) {
    if (this.counter >= 0) {
      this.counter -= 1;
    }
    this.slide(this.counter, delay);
    this.hideButtons();
  }

  slide(counter, delay) {
    this.flashcardContainerTarget.style.setProperty("--counter", counter);
    this.flashcardContainerTarget.style.setProperty("--delay", delay);
  }

  hideButtons() {
    this.nextButtonTarget.classList.toggle(
      "hidden",
      this.counter >= this.numOfCardsValue - 1
    );
    this.prevButtonTarget.classList.toggle("hidden", this.counter <= 0);
  }

  rotate(event) {
    if (!(event.currentTarget instanceof HTMLButtonElement)) return;

    const button = event.currentTarget;
    const flashcardId = button.dataset.flashcardId;

    if (!flashcardId) return;

    const card = this.flashcardTargets.find(
      (card) => card.dataset.flashcardId === flashcardId
    );
    if (!card) return;

    card.classList.toggle("rotated");

    try {
      const video = this.videoTargets.find(
        (video) => video.dataset.flashcardId === flashcardId
      );

      if (video && video.tagName === "VIDEO") {
        if (card.classList.contains("rotated")) {
          video.play();
        } else {
          video.pause();
          video.currentTime = 0;
        }
      }
    } catch (e) {
      console.log("No video found for flashcard or error:", e);
    }
  }

  storeResult(id, result) {
    const flashcardScore = {
      id,
      result,
    };

    const existingIndex = this.result.findIndex((result) => result.id === id);
    if (existingIndex !== -1) {
      this.result[existingIndex] = flashcardScore;
    } else {
      this.result.push(flashcardScore);
    }

    const lastFlashcardId = this.flashcardTargets.at(-1).dataset.flashcardId;
    if (
      lastFlashcardId == id &&
      this.result.find((flashcard) => flashcard.id == lastFlashcardId)
    ) {
      this.sendResult();
    }
  }

  sendResult() {
    fetch(this.storePracticeResultsUrlValue, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(this.result),
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`Server responded with ${response.status}: ${response.statusText}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.redirect) {
          window.location.href = data.url;
        }
      })
      .catch(error => {
      });
  }

  correct(e) {
    const practiceFlashcard = e.currentTarget.closest(".practice-flashcard");
    const flashcardId = practiceFlashcard.dataset.flashcardId;
    this.storeResult(flashcardId, "correct");

    practiceFlashcard.classList.add("correct", "active");
    this.next(this.slidingTransitionDelayValue);
  }

  incorrect(e) {
    const practiceFlashcard = e.currentTarget.closest(".practice-flashcard");
    const flashcardId = practiceFlashcard.dataset.flashcardId;

    this.storeResult(flashcardId, "incorrect");
    practiceFlashcard.classList.add("incorrect", "active");
    this.next(this.slidingTransitionDelayValue);
  }
}
