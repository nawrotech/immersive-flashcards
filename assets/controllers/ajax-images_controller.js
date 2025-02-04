import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
 
    static targets = ['searchTerm', 'flashcardBack']
   
    static values = {
        imagesUrl: String,
        backPrototype: String,
        index: Number
    }

    connect() {
        console.log(this.indexValue);
        // console.log(this.prototypeValue);
        // console.log(this.flashcardBackTargets.at(0));
    }

    async fetchImages(params = {"query": 'cat', 'flashcardType': 'image'}) {

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


    async searchImages(e) {
        const container = e.currentTarget.closest('[data-flashcard-item]');
        if (!container) return;

        console.log(container);

        const backField = this.flashcardBackTargets.find(element => container.contains(element));
        const searchTerm = this.searchTermTargets.find(element => container.contains(element))?.value;

        const backFieldWrapper = backField.closest('.back-wrapper');
        const index = backField.dataset.index;

        try {
            backFieldWrapper.querySelector('.selection-grid')?.remove();

            console.log(searchTerm);

            const data = await this.fetchImages({'query': searchTerm, 'flashcardType': 'image'});
            const imageGridWrapper = this.createImageSelectionWrapperElement();
            data?.forEach(image => {
                const imageEl = this.createImageFieldElement(image, index);

                const imageField = document.createElement('div');
                imageField.classList = 'image-field';
                imageField.innerHTML = imageEl;

                imageGridWrapper.appendChild(imageField);
            });

            backFieldWrapper.appendChild(imageGridWrapper);

            const selectedImage = document.querySelector('.img-radio-btn:checked');
            if (selectedImage) {
                console.log('Selected Image ID:', selectedImage.value);
            }
            

          } catch (error) {
            console.error('Error fetching images:', error);
          }

    }


    selectImage(e) {
        const container = e.currentTarget.closest('[data-flashcard-item]');
        if (!container) return;

        const backField = this.flashcardBackTargets.find(element => container.contains(element));
        backField.value = e.currentTarget.value;
        console.log(backField);

        // console.log(e.currentTarget)
    }


    createImageSelectionWrapperElement() {
        const imageSelectionGrid = document.createElement('div');
        imageSelectionGrid.className = 'selection-grid';

        return imageSelectionGrid;

    }

    createImageFieldElement(image, index) {
        const imageFieldElement = `
                        <label for="${image?.id}">
                            <input data-action="click->ajax-images#selectImage" id="${image?.id}" class="img-radio-btn" name="selected-image-${index}" value="${image?.id}" type="radio">
                            <img class="img" src="${image?.url}" alt="${image?.alt}">
                        </label>
                    `;
        return imageFieldElement;
    }

    searchGifs() {

    }


    createSelectionGrid(images) {
 
    }


      
}
