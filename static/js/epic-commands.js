(function (window) {
	"use strict";

	// Return lines in the selection. Assumes \n as the newline character,
	// however, in unfiltered (i.e., native) contentEditables – the chances
	// of <br /> tags being used to represent newlines is not uncommon.
	function getLines(selection) {
		var lines = selection.toString();
		if (!lines) {
			return [];
		}
		return lines.split('\n');
	}

	// Prefix all given lines
	function prefixLines(lines, prefix) {
		return lines.map(function (line) {
			return prefix + line;
		});
	}

	// A simple helper function to remove currently selected contents and
	// replace it with lines
	function replaceWithLines(document, selection, lines) {

		if (document === null || document === undefined) {
			document = window.document;
		}

		if (selection.rangeCount === 0) {
			return;
		}

		var range = selection.getRangeAt(0);
		range.deleteContents();
		range.collapse(false);

		if (!lines) {
			return;
		}

		var fragment = document.createDocumentFragment();
		lines.forEach(function (line) {
			fragment.appendChild(document.createTextNode(line));
			fragment.appendChild(document.createElement('br'));
		});

		range.insertNode(fragment.cloneNode(true));
	}

	// A simple helper function to insert prefix + postfix in the selection
	// range
	function surroundWith(document, selection, prefix, postfix) {

		// If no document is given, use the default window.document
		if (document === null || document === undefined) {
			document = window.document;
		}

		// If no selection is made, nothing to do
		if (selection.rangeCount === 0) {
			return;
		}

		// If postfix is not given, let prefix == postfix
		if (!postfix) {
			postfix = prefix;
		}

		// Insert the prefix
		var range = selection.getRangeAt(0);
		range.insertNode(document.createTextNode(prefix));

		range.collapse(false);

		// And the postfix
		selection.removeAllRanges();
		selection.addRange(range);
		range.insertNode(document.createTextNode(postfix));

	}

// A very simple command set: bold, italic with no apparent interface...
// This is just a demo, so if you really wish to use Command pattern for this
// please do so.

	var Commands = Object.create(null);
	Commands = {
		bold: function (editor, selection) {
			surroundWith(editor.editorIframeDocument, selection, '**');
		},

		italic: function (editor, selection) {
			surroundWith(editor.editorIframeDocument, selection, '*');
		},

		code: function (editor, selection) {
			surroundWith(editor.editorIframeDocument, selection, '`');
		},

		list: function (editor, selection) {
			var list = prefixLines(getLines(selection), '\t - ');
			replaceWithLines(editor.editorIframeDocument, selection, list);
		}
	};

	window.DefaultCommands = Commands;
}(window));
