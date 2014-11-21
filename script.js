
// DOM objects
var mainnav = document.querySelector("#mainnav");
var editor = document.querySelector("#editor");
var prompt = document.querySelector("#prompt");
var overlay = document.querySelector("#overlay");

var prompt_style_display = "inline-block";

var preview_width = 800;

// null: no file open
// int 0: new / unsaved file
// int 1-X: file X loaded from db
var current_file_id = null;
var current_file_name = null;

// -1: no file open, textarea invisible
// 0: new file, blank
// 1: unsaved file, edited
// 2: saved
var textarea_status = -1;

function change_tas(new_status) {
	getItem("new").show = new_status !== 0;
	getItem("save").show = new_status === 1 && current_file_id > 0;
	getItem("saveas").show = new_status > 0;
	getItem("close").show = new_status !== -1;
	getItem("preview").show = new_status > 0;
	
	textarea_status = new_status;
	print_menu();
}

// do this after dialog unless user cancels
var queued_op = [];
var prompt_visible = false;

function button_new() {
	if (textarea_status === 1) {
		// if unsaved file in editor
		queued_op.push("new");
		button_close();
		return false;
	}
	new_file();
}

function new_file() {
	editor.value = "";
	editor.style.display = "block";
	current_file_id = 0;
	change_tas(0);
}

function button_open() {
	var xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	
	xmlhttp.onreadystatechange = function() {
		var rs = this.readyState;
		var st = this.status;
		//alert("ReadyState: " + rs + ", Status: " + st); 
		if (rs === 4 && st === 200) {
			var response = JSON.parse(this.responseText);
			dialogOpen(response);
		}
	};
	xmlhttp.open("GET", "file.php?action=dir&ajax=yes", true);
	xmlhttp.send();
}

function button_save() {
}
function button_save_as() {
}

function button_close() {
	if (textarea_status === 1) {
		// unsaved file in editor
		if (current_file_id === 0) {
			// file has no slot yet
			// prompt user to SaveAs, Discard, Cancel
			promptUser("unsaved file", false);
			return false;
		}
		if (current_file_id > 0) {
			// file is edited but has slot
			// prompt user to Save, Discard, Cancel
			promptUser("unsaved file", true);
			return false;
		}
	}
	close_file();
}

function close_file() {
	change_tas(-1);
	current_file_id = null;
	current_file_name = null;
	editor.style.display = "none";
	editor.value = "";
}

function button_preview() {
	var fd = new FormData();
	fd.append("text", editor.value);
	
	// save preview to database
	var xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	
	xmlhttp.onreadystatechange = function() {
		var rs = this.readyState;
		var st = this.status;
		if (rs === 4 && st === 200) {
			var ps = document.querySelector("#prompt .previewspinner");
			ps.style.width = preview_width + "px";
			ps.style.height = "60vh";
			//ps.style.height = parseInt(getComputedStyle(overlay).height) * 0.6 + "px";
			ps.innerHTML = "<iframe id='preview' src='emailbody.php?action=echo'></iframe>";
		}
	};
	xmlhttp.open("POST", "file.php?action=savepreview&ajax=yes", true);
	xmlhttp.send(fd);
	
	// show preview overlay with spinner
	button = [];
	button[0] = ["Schließen", function() {
		close_prompt(false);
	}];
	var html = "<h2>Preview</h2><div class='previewspinner'><img src='spinner.gif' /></div><div class='buttons'>";
	for (var i = 0; i < button.length; i++) {
		html += "<a class='button' href='javascript:;' onclick='button_click(button[" + i + "][1]);'>" + button[i][0] + "</a>";
	}
	html += "</div>";
	prompt.innerHTML = html;
	prompt.style.display = prompt_style_display;
	overlay.style.display = "block";
	prompt_visible = true;
}

var button;

function promptUser(text, flag) {
	var message;
	button = [];
	if (text === "unsaved file") {
		// editor contains unsaved file
		if (flag) {
			// has slot
			message = "Datei \"" + current_file_name + "\" wurde verändert.";
			button[0] = ["Speichern", function() {
				save_file(true);
				close_prompt(false);
			}];
		}
		else {
			// no slot yet
			message = "Datei ist nicht gespeichert.";
			button[0] = ["Speichern unter...", function() {
				save_file(false);
				close_prompt(false);
			}];
		}
		button[1] = ["Verwerfen", function () {
			close_file();
			close_prompt(false);
		}];
		button[2] = ["Abbrechen", function () {
			close_prompt(true);
		}];
	}
	
	var html = "<h2><i class='fa fa-warning'></i> " + message + "</h2><div class='content'>";
	html += "</div><div class='buttons'>";
	for (var i = 0; i < button.length; i++) {
		html += "<a class='button' href='javascript:;' onclick='button_click(button[" + i + "][1]);'>" + button[i][0] + "</a>";
	}
	html += "</div>";
	prompt.innerHTML = html;
	prompt.style.display = prompt_style_display;
	overlay.style.display = "block";
	prompt_visible = true;
}

function dialogOpen(r) {
	button = [];
	button[0] = ["Abbrechen", function () {
		close_prompt(true);
	}];
	var html = "<h2><i class='fa fa-folder-open'></i> Datei wählen</h2><div class='content'>";
	for (var i in r) {
		html += "<p>" + r[i].name + "</p>";
	}
	html += "</div><div class='buttons'>";
	for (var i = 0; i < button.length; i++) {
		html += "<a class='button' href='javascript:;' onclick='button_click(button[" + i + "][1]);'>" + button[i][0] + "</a>";
	}
	html += "</div>";
	prompt.innerHTML = html;
	prompt.style.display = prompt_style_display;
	overlay.style.display = "block";
	prompt_visible = true;
}

function button_click(f) {
	f();
}

function close_prompt(cancelled) {
	// true: cancelled
	if (cancelled) queued_op = []; // clear queue
	prompt.style.display = "none";
	overlay.style.display = "none";
	prompt_visible = false;
	editor.focus();
}

function process_queue() {
	if (queued_op === null || queued_op.length === 0) return false;
	var op = queued_op.pop();
	// alert("processing: " + op);
	if (op === "new") new_file();
	if (op === "close") close_file();
	if (op === "save") save_file(true);
	if (op === "save_as") save_file(false);
}

function menu(a) {
	if (a == 0) button_new();
	if (a == 1) button_open();
	if (a == 2) button_save(); // has slot
	if (a == 3) button_save_as(); // has no slot yet
	if (a == 4) button_close();
	if (a == 5) button_preview();
}

var menu_item = [
	{
		name: "new",
		html: "<i class='fa fa-file'></i> Neu",
		f: button_new,
		show: true
	},
	{
		name: "open",
		html: "<i class='fa fa-folder-open'></i> Öffnen...",
		f: button_open,
		show: true
	},
	{
		name: "save",
		html: "<i class='fa fa-save'></i> Speichern",
		f: button_save,
		show: false
	},
	{
		name: "saveas",
		html: "<i class='fa fa-save'></i> Speichern unter...",
		f: button_save_as,
		show: false
	},
	{
		name: "close",
		html: "<i class='fa fa-close'></i> Schließen",
		f: button_close,
		show: false
	},
	{
		name: "preview",
		html: "<i class='fa fa-newspaper-o'></i> Vorschau",
		f: button_preview,
		show: false
	}
];

function getItem(name) {
	var fnd;
	for (var i in menu_item) {
		if (menu_item[i].name === name) return menu_item[i];
	}
	return null;
}

function print_menu() {
	var html = "";
	
	var ml = menu_item.length;
	
	for (var i = 0; i < ml; i++) {
		html += menu_item[i].show ? "<a href='javascript:;' onclick='menu(" + i + ");'>" : "<span>";
		html += menu_item[i].html;
		html += menu_item[i].show ? "</a>" : "</span>";
	}
	
	mainnav.innerHTML = html;
}

function update() {
	editor.style.height = (window.innerHeight - 255) + "px";
}

function queueing() {
	if (prompt_visible) return false;
	process_queue();
}

close_prompt(true);
print_menu();
update();
window.addEventListener("resize", update);
window.setInterval(queueing, 50);

