import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['flashcardFront', 'flashcardBack', 'flashcardImageType', 'collectionContainer', 'removeButton', 'addButton']

    static values = {
        imagesUrl: String,
        index: Number,
        prototype: String,
        maxFlashcardsInDeck: Number,
        serviceLocales: Object
    }

    static classes = ['hidden' ,'flashcardItemWrapper', 'backFieldWrapper', 'selectionGrid', 'loadingSpinner'];

    selectedLanguage = 'en';

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


    selectLanguage(e) {
        const selectedLocale = e.currentTarget.value;
        this.selectedLanguage = this.serviceLocalesValue[selectedLocale]['image_service'];
    }

    addFlashcard()
    {
        const item = document.createElement("li");
        item.className = this.flashcardItemWrapperClass;
        item.dataset.index = this.indexValue;
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
        this.hideAddButton(); 
    }

    deleteFlashcard(event) {
        event.target.closest(`.${this.flashcardItemWrapperClass}`).remove();
        this.hideAddButton(); 
    }

    hideAddButton() {
        this.addButtonTarget.classList.toggle(this.hiddenClass, 
            this.collectionContainerTarget.children.length >= this.maxFlashcardsInDeckValue);
    }

    
    async search(e, imageType, selectedLanguage = '') {
        const flashcardItem = e.currentTarget.closest(`.${this.flashcardItemWrapperClass}`);
        if (!flashcardItem) 
            return;

        const currentFlashcardIndex = flashcardItem.dataset.index;
        const backField = this.flashcardBackTargets.find(element => flashcardItem.contains(element));

        const searchTermField = this.flashcardFrontTargets.find(element => flashcardItem.contains(element));
        if (!searchTermField?.value) 
            return;
        
        const imageTypeField = this.flashcardImageTypeTargets.find(element => flashcardItem.contains(element));
        if (imageTypeField) {
            imageTypeField.value = imageType;
        }
          
        const flashcardButtons = flashcardItem.querySelectorAll('button');
        flashcardButtons.forEach(button => button.disabled = true);

        const backFieldWrapper = backField.closest(`.${this.backFieldWrapperClass}`);

        try {
            backFieldWrapper.querySelector(`.${this.selectionGridClass}`)?.remove();


            const loadingSpinner = this.createLoadingSpinnerElement();
            backFieldWrapper.appendChild(loadingSpinner);

            const images = await this.fetchImages({
                'query': searchTermField?.value, 
                'flashcardType': imageType,
                'lang': selectedLanguage});

            const imageGridWrapper = this.createImageSelectionWrapperElement();


            if (images.length < 1) {
                imageGridWrapper.innerHTML = "<p>No images matched your search, but let's try something else!</p>";
            }
            
            images?.forEach((image) => {
                const imageElement = this.createImageElement(image, currentFlashcardIndex);
                imageGridWrapper.appendChild(imageElement);
            });


            backFieldWrapper.querySelector(`.${this.loadingSpinnerClass}`)?.remove();

            backFieldWrapper.appendChild(imageGridWrapper);
            flashcardButtons.forEach(button => button.disabled = false);

          } catch (error) {
            console.error('Error fetching images:', error);
          }

    }

    searchImages(e) {
        this.search(e, 'image', this.selectedLanguage);
    }

    searchGifs(e) {
        this.search(e, 'gif', this.selectedLanguage);
    }

    clickedElementContainer(e, className) {
        const container = e.currentTarget.closest(`.${className}`);
        if (!container) return;
        return container;
    }

    selectImage(e) {
        const container = this.clickedElementContainer(e, this.flashcardItemWrapperClass);
        const backField = this.flashcardBackTargets.find(element => container.contains(element));
        console.log(backField);
        backField.value = e.currentTarget.value;
    }


    createImageSelectionWrapperElement() {
        const imageSelectionGrid = document.createElement('div');
        imageSelectionGrid.className = this.selectionGridClass;
        return imageSelectionGrid;
    }


    createImageElement(image, index) {
        const imageElement = document.createElement('div');
        imageElement.className = 'image-field';

        imageElement.innerHTML = `
                        <label for="${image?.id}">
                            <input data-action="click->ajax-images#selectImage"
                                name="selected-image-${index}" 
                                id="${image?.id}" 
                                class="img-radio-btn" 
                                value="${image?.url}"
                                type="radio">
                            <img class="img" src="${image?.url}" alt="${image?.alt}">
                        </label>
                    `;
        return imageElement;
    }

    createLoadingSpinnerElement() {
        const loadingSpinnerElement = document.createElement('span');
        loadingSpinnerElement.classList.add(this.loadingSpinnerClass);
        return loadingSpinnerElement;
    }

      
}
