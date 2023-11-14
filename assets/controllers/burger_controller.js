import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    initialize() {
        const body = document.querySelector('body');
        const burger = document.getElementById('burger');
        const menu = document.querySelector('body > nav ul');

        burger.addEventListener('click', event);
        document.querySelector('.dimmed').addEventListener('click', event);

        function event() {
            if (!burger.classList.contains('active')) {
                body.classList.add('overflow-hidden');
                burger.classList.add('active');
                menu.classList.add('active');
            } else {
                body.classList.remove('overflow-hidden');
                burger.classList.remove('active');
                menu.classList.remove('active');
            }

            return false;
        }
    }
}
