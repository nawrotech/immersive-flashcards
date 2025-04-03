import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static targets = ['collectionContainer', 'removeButton']
    static values = {
        index: Number,
        prototype: String,
        wrapperClassName: String
    }
    
    connect() {
        console.log(this.prototypeValue);
    }

    addCollectionElement()
    {
        const item = document.createElement("li");
        item.classList = this.wrapperClassNameValue;

        const flashcardItem = document.createElement('div');
        flashcardItem.className = 'flashcard-item';

        flashcardItem.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);

        item.appendChild(flashcardItem);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    removeElement(event) {
        event.target.closest(`.${this.wrapperClassNameValue}`).remove();
    }
}
