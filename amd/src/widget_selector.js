// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tiny snippet - Widgets Page
 *
 * @module      tiny_snippet/widgets page
 * @copyright   2023 Justin Hunt <justin@poodll.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';
import {getConfig} from './options';

import * as ModalFactory from 'core/modal_factory';
import * as Templates from 'core/templates';
import {prefetchStrings, prefetchTemplates} from 'core/prefetch';
import Modal from "./modal";
import ModalRegistry from 'core/modal_registry';

import {
    component,
    CSS,
} from './common';

/**
 * The main snippet code
 */
export default class {

    /**
     * Constructor for the Tiny Snippet Widgets Page
     *
     * @param {TinyMCE} editor The Editor to which the content will be inserted
     * @param {elementid} elementid
     * @param {Modal} modal The Moodle Modal that contains the interface used for recording
     * @param {config} config The data passed to template and used internally for managing plugin state
     */
    constructor(editor,elementid, modal, config) {
        this.ready = false;
        this.editor = editor;
        this.elementid = elementid;
        this.config = config;//getData(editor).params;
        this.modal = modal;
        this.modalRoot = modal.getRoot()[0];
    }

    init(){
        this.registerEvents();
        this.ready = true;
    }

    /**
     * Close the modal and stop recording.
     */
    close() {
        // Closing the modal will destroy it and remove it from the DOM.
        // It will also stop the recording via the hidden Modal Event.
        this.modal.hide();
    }

    getElement(component){
        return this.modalRoot.querySelector('#' + this.elementid + '_' + component);
    }

    /**
     * Register event listeners for the modal.
     */
    registerEvents() {
        var that =this;
        const $root = this.modal.getRoot();
        const root = $root[0];
        //const widgetbuttons = root.getElementsByClassName('tiny_snippet_widgetbutton');

        ///listen for button clicks
        root.addEventListener("click", function(e) {
            var widgetchosen = e.target.closest(".tiny_snippet_widgetchoosebutton");
            var widgetinserted = e.target.closest(".tiny_snippet_widgetinsertbutton");
            var widgetcancelled = e.target.closest(".tiny_snippet_widgetcancelledbutton");

            //If widget chosen from widget page (chooser)
            if (widgetchosen) {
                e.preventDefault();
                //get our widget from the configs

                var templateindex = e.target.getAttribute('data-templateindex');
                var widget = that.getWidget(templateindex);

                if (widget === null) {
                    Log.debug("That template not found: " + templateindex);
                    Log.debug(that.config);
                    return;
                } else {
                    //show the widget form
                    that.showOptionsPanel(e, widget);
                }
            }

            //If widget inserted from widget options page
            if (widgetinserted) {
                e.preventDefault();
                var templateindex = e.target.getAttribute('data-templateindex');
                var widget = that.getWidget(templateindex);
                if (widget === null) {
                    Log.debug("That template not found: " + templateindex);
                    Log.debug(that.config);
                    return;
                } else {
                    //get the filter string
                    var filterstring = that.getFilterString(widget);
                    //insert the filter string into the editor
                    that.editor.insertContent(filterstring);
                    //close the modal
                    that.close();
                }
            }

            //If widget options cancelled
            if (widgetcancelled) {
                e.preventDefault();

                //show the widget form
                that.hideOptionsPanel(e, widget);

            }
        });//end of button click listener

    }//end of register events

    getWidget(templateindex) {
        var widget = null;
        for (var i = 0; i < this.config.widgets.length; i++) {
            if (this.config.widgets[i].templateindex == templateindex) {
                widget = this.config.widgets[i];
                break;
            }
        }
        return widget;
    }

    /**
     * Display the chosen widgets template form
     *
     * @method showTemplateForm
     * @private
     * @param {Event} e The event that triggered the action
     * @param {Object} widget The widget object
     */
    showOptionsPanel(e, widget) {
        var that = this;
        Log.debug('showing the template form for: ' + widget.name);

        that.modalRoot.querySelector('#tiny_snippet_widgets_optionspanel');
        Templates.render('tiny_snippet/widgetoptions', widget).then(function (html, js) {
            var optionspanel = that.modalRoot.querySelector('#tiny_snippet_widgets_optionspanel');
            var selectorpanel = that.modalRoot.querySelector('#tiny_snippet_widgets_selectorpanel');
            Log.debug('replacing contents of options panel');

            //this would fail if the options panel had already made in insert, and was re-used. Weird.
            //replaced with: optionspanel.innerHTML = html;
            //Templates.replaceNodeContents('#tiny_snippet_widgets_optionspanel', html, js);
            optionspanel.innerHTML = html;


            //hide and show the selector and options panels
            selectorpanel.classList.add('tiny_snippet_hidden');
            optionspanel.classList.remove('tiny_snippet_hidden');
            //trigger the animations
            optionspanel.style.left=0;
            selectorpanel.style.left= selectorpanel.offsetWidth * -1 + 'px';
        }).catch(
            function (e){Log.debug(e);}
        );

    }

    /**
     * Display the chosen widgets template form
     *
     * @method showTemplateForm
     * @private
     */
    hideOptionsPanel() {
        var that = this;
        Log.debug('hiding the template options form ');

        //animate it out
        var optionspanel = that.modalRoot.querySelector('#tiny_snippet_widgets_optionspanel');
        var selectorpanel = that.modalRoot.querySelector('#tiny_snippet_widgets_selectorpanel');
        //hide and show the selector and options panels
        selectorpanel.classList.remove('tiny_snippet_hidden');
        optionspanel.classList.add('tiny_snippet_hidden');
        //trigger the animations
        optionspanel.style.left=optionspanel.offsetWidth  +  'px';
        selectorpanel.style.left=0;
    }

    /**
     * Inserts the users input onto the page
     * @method _getWidgetsInsert
     * @private
     * @param {Object} widget The widget object
     */
    getFilterString(widget) {

        var that = this;
        var retstring = widget.body;

        //Do the merge
        var thefields =  that.modalRoot.querySelectorAll('.tiny_snippet_templatevariable_' + widget.templateindex);
        if(thefields !== null && thefields !== 'undefined' && thefields.length > 0){
            thefields.forEach((thefield) =>{
                var thevalue = thefield.value;
                var thekey = thefield.getAttribute('data-key');
                retstring = retstring.replace(new RegExp('{{' + thekey + '}}', 'g'), thevalue);
            });
        }
        return retstring;

    }



    static generateRandomString() {
        var length = 8;
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = '';

        for (var i = 0; i < length; i++) {
            var randomIndex = Math.floor(Math.random() * characters.length);
            result += characters.charAt(randomIndex);
        }

        return result;
    }

    static getModalClass() {
        const modalType = `${component}/widgetselector`;
        const registration = ModalRegistry.get(modalType);
        if (registration) {
            return registration.module;
        }

        const WidgetModal = class extends Modal {
            static TYPE = modalType;
            static TEMPLATE = `${component}/widgetselector`;
        };

        ModalRegistry.register(WidgetModal.TYPE, WidgetModal, WidgetModal.TEMPLATE);
        return WidgetModal;
    }

    static getModalContext(editor) {

        var context = {};
        var config = getConfig(editor);
        Log.debug(config);
        
        //stuff declared in common
        context.CSS = CSS;

        //insert method
        context.widgets = config.widgets;

        return context;
    }

    static async display(editor) {
        const ModalClass = this.getModalClass();
        const templatecontext = this.getModalContext(editor);
        const elementid = this.generateRandomString();

        const modal = await ModalFactory.create({
            type: ModalClass.TYPE,
            templateContext: templatecontext,
            large: true,
        });

        // Set up the Widgets Panel and show the modal
        const widgetspanel = new this(editor, elementid, modal, templatecontext);
        widgetspanel.init();

        modal.show();
        return modal;
    }

} //end of class