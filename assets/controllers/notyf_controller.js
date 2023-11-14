import { Controller } from '@hotwired/stimulus';

import { Notyf } from 'notyf/notyf'
import 'notyf/notyf.min.css';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    initialize() {
        const notyf = new Notyf({
            duration: 5000,
            dismissible: true,
            position: {
                x: 'center',
                y: 'bottom',
            },
            types: [
                {
                    type: 'error',
                    background: 'indianred',
                }
            ]
        });

        document.querySelectorAll('[data-controller="notyf"]').forEach(function(e) {
            if (e.dataset.type === 'success') {
                notyf.success(e.dataset.content);
            }
            if (e.dataset.type === 'error') {
                notyf.error(e.dataset.content);
            }
        });
    }
}
