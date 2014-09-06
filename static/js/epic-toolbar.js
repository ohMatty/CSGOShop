(function (window, document) {
	"use strict";

// Define a Toolbar object which deals with commands issued to an instance
// of EpicEditor.

	var Toolbar = function (id, editor, commands) {
		this.container = document.getElementById(id);
		this.editor = editor;
		this.commands = commands;

		if (!id) {
			throw new Error('Unable to find toolbar container: ' + id);
		}

		if (!(editor instanceof EpicEditor)) {
			throw new Error('You must provide an instance of EpicEditor');
		}

// Let's hard wire in the commands list for now


// Tap into HTML's data-command attribute and trigger command on click

		this.container.addEventListener('click', function (event) {
			var target = event.target;
			var command = target.getAttribute('data-command');
			this.executeCommand(command);
		}.bind(this), false);
	};

// Toolbar object has a very simple interface, just execute..

	Toolbar.prototype = {
		executeCommand: function (command) {

			if (!command) {
				return;
			}

// Each command is called with two arguments: editor and current selection.
// Note that selection object can be null.

			var selection = this.editor.editorIframeDocument.getSelection();
			this.commands[command](this.editor, selection);

		}
	};

	window.Toolbar = Toolbar;
}(window, window.document));
