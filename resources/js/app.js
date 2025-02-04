import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faArrowsRotate, faCoins, faEdit, faEye, faFileInvoice, faFileInvoiceDollar, faFilePdf, faFloppyDisk, faHandHoldingDollar, faHouse, faMoneyBill1, faSquareArrowUpRight, faUserPlus, faUsers, faUserSecret, faXmark } from '@fortawesome/free-solid-svg-icons';

library.add(faUserSecret);
library.add(faHouse);
library.add(faUsers);
library.add(faUserPlus);
library.add(faEye);
library.add(faEdit);
library.add(faFloppyDisk);
library.add(faFilePdf);
library.add(faMoneyBill1);
library.add(faCoins);
library.add(faFileInvoice);
library.add(faHandHoldingDollar);
library.add(faSquareArrowUpRight);
library.add(faXmark);
library.add(faArrowsRotate);
library.add(faFileInvoiceDollar);

const toast_options = {
    timeout: 3000,
    position: "bottom-right",
    closeOnClick: true,
}

// const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const appName = 'Sower | Loan Management System';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, toast_options)
            .component('font-awesome-icon', FontAwesomeIcon)
            .mount(el);
    },
    progress: {
        color: '#338855',
    },
});
