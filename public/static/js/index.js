let Keyboard = window.SimpleKeyboard.default;


let selectedInput;

let keyboard = new Keyboard({
  onChange: input => onChange(input),
  onKeyPress: button => onKeyPress(button),
  layout: {
    default: [
      "` 1 2 3 4 5 6 7 8 9 0 - = {bksp}",
      "{tab} q w e r t y u i o p [ ] \\",
      "{lock} a s d f g h j k l ; ' {enter}",
      "{shift} z x c v b n m , . / {shift}",
      ".com @"
    ],
    shift: [
      "~ ! @ # $ % ^ & * ( ) _ + {bksp}",
      "{tab} Q W E R T Y U I O P { } |",
      '{lock} A S D F G H J K L : " {enter}',
      "{shift} Z X C V B N M < > ? {shift}",
      ".com @"
    ]
  }
});



document.querySelectorAll(".input").forEach(input => {
  input.addEventListener("focus", onInputFocus);
  input.addEventListener("input", onInputChange);
});

function onInputFocus(event) {
  selectedInput = `#${event.target.id}`;

  keyboard.setOptions({
    inputName: event.target.id
  });
}

function onInputChange(event) {
  keyboard.setInput(event.target.value, event.target.id);
}

function onChange(input) {
  // console.log("Input changed", input);
  document.querySelector(selectedInput || ".input").value = input;
}

function onKeyPress(button) {
  // console.log("Button pressed", button);

  /**
   * Shift functionality
   */
  if (button === "{lock}" || button === "{shift}") handleShiftButton();
}

function handleShiftButton() {
  let currentLayout = keyboard.options.layoutName;
  let shiftToggle = currentLayout === "default" ? "shift" : "default";

  keyboard.setOptions({
    layoutName: shiftToggle
  });
}




