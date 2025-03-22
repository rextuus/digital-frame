import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay']; // Define the spinner/overlay as a target
    connect() {
        console.log('Form Loading Controller connected!');
    }

    startLoading(event) {
        // Prevent the form from automatically submitting and refreshing the page
        console.log(this.overlayTarget);
        this.overlayTarget.style.setProperty('display', 'flex', 'important');
    }
}