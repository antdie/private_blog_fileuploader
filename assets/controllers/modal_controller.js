import { Controller } from '@hotwired/stimulus';

import 'bootstrap/js/dist/modal';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    initialize() {
        document.querySelectorAll('[data-controller="modal"]').forEach(function(e) {
            e.addEventListener('hide.bs.modal', function() {
                // Pause played media when modal is closed
                const iframe = e.querySelector('iframe');
                const audio = e.querySelector('audio');
                const video = e.querySelector('video');

                if (iframe) {
                    iframe.src = iframe.src;
                }
                if (audio) {
                    audio.pause();
                }
                if (video) {
                    video.pause();
                }
            });
        });
    }
}
