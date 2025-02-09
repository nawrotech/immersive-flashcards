import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['flashcardFront', 'flashcardBack', 'flashcardImageType', 'collectionContainer', 'removeButton', ]
   
    static values = {
        imagesUrl: String,
        index: Number,
        prototype: String,
        wrapperClassName: String
    }

    addFlashcard()
    {
        const item = document.createElement("li");
        item.classList = this.wrapperClassNameValue;
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;

    }

    deleteFlashcard(event) {
        event.target.closest(`.${this.wrapperClassNameValue}`).remove();
    }

    async search(e, imageType) {
        const container = e.currentTarget.closest(`.${this.wrapperClassNameValue}`);
        if (!container) 
            return;

        const backField = this.flashcardBackTargets.find(element => container.contains(element));

        const searchTermField = this.flashcardFrontTargets.find(element => container.contains(element));
        if (!searchTermField?.value) 
            return;
        
        const imageTypeField = this.flashcardImageTypeTargets.find(element => container.contains(element));
        imageTypeField.value = imageType;

        const backFieldWrapper = backField.closest('.back-wrapper');

        try {
            backFieldWrapper.querySelector('.selection-grid')?.remove();

            const images = await this.fetchImages({'query': searchTermField?.value, 'flashcardType': imageType});
            const imageGridWrapper = this.createImageSelectionWrapperElement();
            
            images?.forEach(image => {
                const imageElement = this.createImageElement(image, this.indexValue);
                imageGridWrapper.appendChild(imageElement);
            });
            backFieldWrapper.appendChild(imageGridWrapper);

          } catch (error) {
            console.error('Error fetching images:', error);
          }

    }

    searchImages(e) {
        this.search(e, 'image');
    }

    searchGifs(e) {
        this.search(e, 'gif');
    }

    clickedElementContainer(e, className) {
        const container = e.currentTarget.closest(className);
        if (!container) return;
        return container;
    }


    selectImage(e) {
        const container = this.clickedElementContainer(e, '.flashcard-item');
        const backField = this.flashcardBackTargets.find(element => container.contains(element));
        backField.value = e.currentTarget.value;
    }


    createImageSelectionWrapperElement() {
        const imageSelectionGrid = document.createElement('div');
        imageSelectionGrid.className = 'selection-grid';
        return imageSelectionGrid;
    }

    createImageElement(image, index) {
        const imageElement = document.createElement('div');
        imageElement.classList = 'image-field';
        imageElement.innerHTML = `
                        <label for="${image?.id}">
                            <input data-action="click->ajax-images#selectImage"
                             id="${image?.id}" 
                             class="img-radio-btn" 
                             name="selected-image-${index}" 
                             value="${image?.id}"
                             type="radio">
                            <img class="img" src="${image?.url}" alt="${image?.alt}">
                        </label>
                    `;
        return imageElement;
    }

    
    async fetchImages(params) {
        const queryString = new URLSearchParams(params).toString();
        return fetch(`${this.imagesUrlValue}?${queryString}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json(); 
                })
                .then(data => data)
                .catch(error => console.error('Error fetching images:', error));
    
    }


      
}
