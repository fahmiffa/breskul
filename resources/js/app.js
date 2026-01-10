import Alpine from "alpinejs";
import "trix";
import "trix/dist/trix.css";
import './alignment-elements.js';

window.Trix = Trix; // Don't need to bind to the window, but useful for debugging.
Trix.config.toolbar.getDefaultHTML = toolbarDefaultHTML;

Trix.config.blockAttributes.alignLeft = {
  tagName: 'align-left',
  parse: false,
  nestable: false,
  exclusive: true,
};

Trix.config.blockAttributes.alignCenter = {
  tagName: 'align-center',
  parse: false,
  nestable: false,
  exclusive: true,
};

Trix.config.blockAttributes.alignRight = {
  tagName: 'align-right',
  parse: false,
  nestable: false,
  exclusive: true,
};

// trix-before-initialize runs too early.
// We only need to do this once. Everything after initialize will get the
// defaultHTML() call automatically.
document.addEventListener('trix-initialize', updateToolbars, { once: true });

function updateToolbars(event) {
  const toolbars = document.querySelectorAll('trix-toolbar');
  const html = Trix.config.toolbar.getDefaultHTML();
  toolbars.forEach((toolbar) => (toolbar.innerHTML = html));
}


function toolbarDefaultHTML() {
  const { lang } = Trix.config;
  return `
  <div class="trix-button-row">
    <span class="trix-button-group trix-button-group--alignment-tools">
      <button type="button" class="trix-button trix-button--icon trix-button--icon-align-left" data-trix-attribute="alignLeft" title="Align Left" tabindex="-1">Align Left</button>
      <button type="button" class="trix-button trix-button--icon trix-button--icon-align-center" data-trix-attribute="alignCenter" title="Align Left" tabindex="-1">Align Center</button>
      <button type="button" class="trix-button trix-button--icon trix-button--icon-align-right" data-trix-attribute="alignRight" title="Align Right" tabindex="-1">Align Right</button>
    </span>
    <span class="trix-button-group trix-button-group--text-tools" data-trix-button-group="text-tools">
      <button type="button" class="trix-button trix-button--icon trix-button--icon-bold" data-trix-attribute="bold" data-trix-key="b" title="${lang.bold}" tabindex="-1">${lang.bold}</button>
      <button type="button" class="trix-button trix-button--icon trix-button--icon-italic" data-trix-attribute="italic" data-trix-key="i" title="${lang.italic}" tabindex="-1">${lang.italic}</button>
    </span>
    <span class="trix-button-group trix-button-group--block-tools" data-trix-button-group="block-tools">
      <button type="button" class="trix-button trix-button--icon trix-button--icon-bullet-list" data-trix-attribute="bullet" title="${lang.bullets}" tabindex="-1">${lang.bullets}</button>
      <button type="button" class="trix-button trix-button--icon trix-button--icon-number-list" data-trix-attribute="number" title="${lang.numbers}" tabindex="-1">${lang.numbers}</button>
    </span>
    <span class="trix-button-group-spacer"></span>
    <span class="trix-button-group trix-button-group--history-tools" data-trix-button-group="history-tools">
      <button type="button" class="trix-button trix-button--icon trix-button--icon-undo" data-trix-action="undo" data-trix-key="z" title="${lang.undo}" tabindex="-1">${lang.undo}</button>
      <button type="button" class="trix-button trix-button--icon trix-button--icon-redo" data-trix-action="redo" data-trix-key="shift+z" title="${lang.redo}" tabindex="-1">${lang.redo}</button>
    </span>
  </div>
`;
}


import {
    layout,
    dataTable,
    salesChart,
    payChart,
    countUp,
    currencyInput,
    generateBill,
    generateImport,
    trixEditor,
    generateStudentsImport,
    jadwalForm,
    extraForm
} from "./custom.js";

window.Alpine = Alpine;
Alpine.data("layout", layout);
Alpine.data("dataTable", dataTable);
Alpine.data("salesChart", salesChart);
Alpine.data("payChart", payChart);
Alpine.data("countUp", countUp);
Alpine.data('currencyInput', currencyInput);
Alpine.data('generateBill', generateBill);
Alpine.data('generateImport', generateImport);
Alpine.data('trixEditor', trixEditor);
Alpine.data('generateStudentsImport', generateStudentsImport);
Alpine.data('jadwalForm', jadwalForm);
Alpine.data('extraForm', extraForm);
Alpine.start();
