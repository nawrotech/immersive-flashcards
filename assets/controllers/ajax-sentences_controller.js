import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "sentencesContainer",
    "searchButton",
    "audio",
    "soundButton",
  ];

  static classes = [
    "loadingSpinner",
    "soundButton",
    "hidden",
    "sentenceList",
    "sentenceItem",
    "author",
    "flow",
    "error",
  ];

  static values = {
    sentencesUrl: String,
    audioSrc: String,
    csrfToken: String
  };

  connect() {
    console.log(this.csrfTokenValue);
  }

  async fetchSentences(query) {
    const queryString = new URLSearchParams({ query }).toString();
    try {
      const response = await fetch(`${this.sentencesUrlValue}&${queryString}`, {
        method: "GET",
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': this.csrfTokenValue
        }
      });
      if (!response.ok) {
        throw new Error(
          `Server error: ${response.status} ${response.statusText}`
        );
      }
      return await response.json();
    } catch (error) {
      if (error instanceof TypeError) {
        throw new Error("Network error: Please check your connection");
      }
      throw error;
    }
  }

  async searchSentences({ currentTarget, params: { query } }) {
    const buttonId = Number(currentTarget.dataset.ajaxSentencesId);
    const container = this.findSentencesContainerById(buttonId);

    if (!container instanceof HTMLDivElement || !container) return;

    container.innerHTML = "";
    const loadingSpinner = this.createLoadingSpinnerElement();
    container.appendChild(loadingSpinner);

    try {
      const sentences = await this.fetchSentences(query);

      if (!sentences || sentences.length === 0) {
        container.innerHTML = "";
        const errorElement = this.createErrorMessageElement(
          "No examples found for this word. Try searching with a different word!"
        );
        container.appendChild(errorElement);
        return;
      }

      const sentencesList = this.createSenteceListElement();
      sentences.forEach((sentence) => {
        const sentenceItem = this.createSentenceItemElement(sentence);
        if (sentence?.audioUrl) {
          const audioElement = this.createAudioComponentElement(
            sentence?.audioUrl
          );
          sentenceItem.appendChild(audioElement);
        }
        sentencesList.appendChild(sentenceItem);
      });

      container.innerHTML = "";
      container.appendChild(sentencesList);
    } catch (error) {
      container.innerHTML = "";
      const errorElement = this.createErrorMessageElement(
        "Something went wrong, please try again later!"
      );
      container.appendChild(errorElement);
    }
  }

  createSenteceListElement() {
    const sentencesList = document.createElement("ul");
    sentencesList.classList.add(...this.sentenceListClasses);
    return sentencesList;
  }

  createSentenceItemElement({ text, author }) {
    const sentenceItem = document.createElement("li");
    sentenceItem.innerHTML = `<div class="${this.flowClass}">
                                    <p>${text}</p>
                                    ${
                                      author
                                        ? `<em class="${this.authorClass}">Author: ${author}</em>`
                                        : ""
                                    }
                               </div>`;
    sentenceItem.classList.add(this.sentenceItemClass);
    return sentenceItem;
  }

  createLoadingSpinnerElement() {
    const loadingSpinner = document.createElement("span");
    loadingSpinner.classList.add(this.loadingSpinnerClass);
    return loadingSpinner;
  }

  createAudioComponentElement(audioSrc) {
    const audioContainer = document.createElement("div");

    const soundButtonElement = this.createSoundButtonElement();
    soundButtonElement.setAttribute(
      "data-ajax-sentences-audio-src-value",
      audioSrc
    );
    soundButtonElement.setAttribute(
      "data-ajax-sentences-target",
      "soundButton"
    );
    soundButtonElement.setAttribute(
      "data-action",
      "click->ajax-sentences#playAudio"
    );

    audioContainer.appendChild(soundButtonElement);
    return audioContainer;
  }

  createAudioElement(audioSrc) {
    const audioElement = document.createElement("audio");
    audioElement.src = audioSrc;
    audioElement.classList.add(this.hiddenClass);
    audioElement.setAttribute("data-ajax-sentences-target", "audio");
    return audioElement;
  }

  createSoundButtonElement() {
    const soundButtonElement = document.createElement("button");
    soundButtonElement.innerHTML =
      '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="currentColor" d="M8.694 2.04A.5.5 0 0 1 9 2.5v11a.5.5 0 0 1-.85.357l-2.927-2.875H3.5a1.5 1.5 0 0 1-1.5-1.5v-2.99a1.5 1.5 0 0 1 1.5-1.5h1.724l2.927-2.85a.5.5 0 0 1 .543-.103M8 3.684L5.777 5.851a.5.5 0 0 1-.35.142H3.5a.5.5 0 0 0-.5.5v2.989a.5.5 0 0 0 .5.5h1.928a.5.5 0 0 1 .35.143L8 12.308zm2.111 1.504a.5.5 0 0 1 .703-.08l.002.001l.002.002l.005.004l.015.013l.046.04q.055.05.142.142c.113.123.26.302.405.54c.291.48.573 1.193.573 2.148c0 .954-.282 1.668-.573 2.148a3.4 3.4 0 0 1-.405.541a3 3 0 0 1-.202.196l-.008.007h-.001s-.447.243-.703-.078a.5.5 0 0 1 .075-.7l.002-.002l-.001.001l.002-.001h-.001l.018-.016q.028-.025.085-.085a2.4 2.4 0 0 0 .284-.382c.21-.345.428-.882.428-1.63s-.218-1.283-.428-1.627a2.4 2.4 0 0 0-.368-.465l-.018-.016a.5.5 0 0 1-.079-.701m1.702-2.08a.5.5 0 1 0-.623.782l.011.01l.052.045q.072.063.201.195c.17.177.4.443.63.794c.46.701.92 1.733.92 3.069a5.5 5.5 0 0 1-.92 3.065c-.23.35-.46.614-.63.79a4 4 0 0 1-.252.24l-.011.01h-.001a.5.5 0 0 0 .623.782l.033-.027l.075-.065c.063-.057.15-.138.253-.245a6.4 6.4 0 0 0 .746-.936a6.5 6.5 0 0 0 1.083-3.614a6.54 6.54 0 0 0-1.083-3.618a6.5 6.5 0 0 0-.745-.938a5 5 0 0 0-.328-.311l-.023-.019l-.007-.006l-.002-.002zM10.19 5.89l-.002-.001Z"/></svg>';
    soundButtonElement.classList.add(this.soundButtonClass);
    soundButtonElement.classList.add("flashcard-practice-btn");
    return soundButtonElement;
  }

  findSentencesContainerById(id) {
    if (typeof id != "number") {
      console.error("Passed id is not a number");
    }

    return this.sentencesContainerTargets.find(
      (container) => Number(container.dataset.ajaxSentencesId) === id
    );
  }

  createErrorMessageElement(errorMessage) {
    const errorElement = document.createElement("p");
    errorElement.classList.add(this.errorClass);
    errorElement.textContent = errorMessage;
    return errorElement;
  }

  playAudio({ currentTarget }) {
    this.audioTargets.forEach(audio => {
      audio.pause();
      audio.currentTime = 0;
    });

    const button = currentTarget;
    const audioSrc = button.dataset.ajaxSentencesAudioSrcValue;
    const audioContainer = button.parentElement;

    let audioElement = this.audioTargets.find(
      (audio) => audio.parentElement === audioContainer
    );

    if (!audioElement) {
      audioElement = this.createAudioElement(audioSrc);
      button.parentNode.appendChild(audioElement);
    } else if (audioElement.src !== audioSrc) {
      audioElement.src = audioSrc;
    }

    audioElement.play();
  }
}
