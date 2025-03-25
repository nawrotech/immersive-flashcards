import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['flashcardFront', 'flashcardBack', 'flashcardImageType', 'collectionContainer', 'removeButton', 'addButton']

    static values = {
        imagesUrl: String,
        index: Number,
        prototype: String,
        maxFlashcardsInDeck: Number,
        serviceLocales: Object,
        defaultLanguage: String
    }

    static classes = ['hidden' ,'flashcardItemWrapper', 'backFieldWrapper', 'selectionGrid', 'loadingSpinner', 'imageField', 'imageRadioButton', 'errorMessage'];

    selectedLanguage = this.defaultLanguageValue;

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
                .catch(error => {
                    console.error('Error fetching images:', error);
                    throw error;
                });
    
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
        if (flashcardItem.tagName !== "LI") 
            return;

        const currentFlashcardIndex = flashcardItem.dataset.index;
        const backField = this.flashcardBackTargets.find(element => flashcardItem.contains(element));
        if (backField.tagName !== "INPUT") 
            return;
        
        const searchTermField = this.flashcardFrontTargets.find(element => flashcardItem.contains(element));
        if (searchTermField.tagName !== "INPUT" || !searchTermField?.value) 
            return;
        
        const imageTypeField = this.flashcardImageTypeTargets.find(element => flashcardItem.contains(element));
        if (imageTypeField.tagName !== "INPUT") 
            return;

        imageTypeField.value = imageType;
     
     
        const backFieldWrapper = backField?.closest(`.${this.backFieldWrapperClass}`);
        if (backFieldWrapper.tagName !== "DIV") 
            return;
        this.manageFlashcardItemButtons(flashcardItem, true);
        this.clearBackFieldWrapper(backFieldWrapper);
        
        const loadingSpinner = this.createLoadingSpinnerElement();
        backFieldWrapper.appendChild(loadingSpinner);

        try {
            const images = await this.fetchImages({
                'query': searchTermField?.value, 
                'flashcardType': imageType,
                'lang': selectedLanguage});

            if (!images) {
                this.clearBackFieldWrapper(backFieldWrapper);
                this.manageFlashcardItemButtons(flashcardItem, false);
                const errorParagraph = this.createErrorMessageElement();
                backFieldWrapper.appendChild(errorParagraph);
                return;
            }

            if (images.length < 1) {
                this.manageFlashcardItemButtons(flashcardItem, false);
                this.clearBackFieldWrapper(backFieldWrapper);
                const errorParagraph = this.createErrorMessageElement(`No images matched your search, but let's try something else!`);
                backFieldWrapper.appendChild(errorParagraph);
                return;
            }
            
            const imageGridWrapper = this.createImageSelectionWrapperElement();
            
            images?.forEach((image) => {
                const imageElement = this.createImageElement(image, currentFlashcardIndex);
                imageGridWrapper.appendChild(imageElement);
            });

            this.clearBackFieldWrapper(backFieldWrapper);
            this.manageFlashcardItemButtons(flashcardItem, false);
            backFieldWrapper.appendChild(imageGridWrapper);



          } catch (error) {
            this.manageFlashcardItemButtons(flashcardItem, false);
            this.clearBackFieldWrapper(backFieldWrapper);
            const errorParagraph = this.createErrorMessageElement();
            backFieldWrapper.appendChild(errorParagraph);
          }
    }


    clearBackFieldWrapper(backFieldWrapper) {
        if (backFieldWrapper.className !== this.backFieldWrapperClass) 
            return;
        
        backFieldWrapper.querySelector(`.${this.loadingSpinnerClass}`)?.remove();
        backFieldWrapper.querySelector(`.${this.selectionGridClass}`)?.remove();
        backFieldWrapper.querySelector(`.${this.errorMessageClass}`)?.remove();
    }

    manageFlashcardItemButtons(flashcardItem, isDisabled = false) {
        if (flashcardItem.className !== this.flashcardItemWrapperClass) 
            return;

        if (typeof isDisabled !== 'boolean') {
            return;
        }

        const flashcardButtons = flashcardItem.querySelectorAll('button');
        flashcardButtons.forEach(button => button.disabled = isDisabled);
    }

    searchImages(e) {
        this.search(e, 'image', this.selectedLanguage);
    }

    searchGifs(e) {
        this.search(e, 'gif', this.selectedLanguage);
    }

    clickedFlashcardContainer(e, className) {
        const container = e.currentTarget.closest(`.${className}`);
        if (!container) return;
        return container;
    }

    selectImage(e) {
        const container = this.clickedFlashcardContainer(e, this.flashcardItemWrapperClass);
        const backField = this.flashcardBackTargets.find(element => container.contains(element));
        backField.value = e.currentTarget.value;
    }

    createErrorMessageElement(errorMessage = 'An error occurred, please try again') {
        const errorParagraph = document.createElement('p');
        errorParagraph.classList.add(this.errorMessageClass);
        errorParagraph.textContent = errorMessage;
        return errorParagraph;
    }

    createImageSelectionWrapperElement() {
        const imageSelectionGrid = document.createElement('div');
        imageSelectionGrid.classList.add(this.selectionGridClass);
        return imageSelectionGrid;
    }


    createImageElement(image, index) {
        const imageElement = document.createElement('div');
        imageElement.classList.add(this.imageFieldClass);
        imageElement.innerHTML = `
                        <label for="${image?.id}">
                            <input data-action="click->ajax-images#selectImage"
                                name="selected-image-${index}" 
                                id="${image?.id}" 
                                class=${this.imageRadioButtonClass}
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
