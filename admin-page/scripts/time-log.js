const dropdown = document.getElementById("noteReason");
  const inputField = document.getElementById("customNoteInput");

  function handleNoteChange() {
    if (dropdown.value === "Other") {
      dropdown.style.display = "none";
      inputField.style.display = "inline-block";
      inputField.focus();
    }
  }

  function handleInputConfirm() {
    const userInput = inputField.value.trim();
    if (userInput) {
      // Check if option already exists
      let found = false;
      for (let i = 0; i < dropdown.options.length; i++) {
        if (dropdown.options[i].value === userInput) {
          found = true;
          break;
        }
      }

      if (!found) {
        const newOption = document.createElement("option");
        newOption.value = userInput;
        newOption.text = userInput;
        dropdown.add(newOption, dropdown.options[dropdown.options.length - 1]); // add before 'Other'
        dropdown.value = userInput;
      }

      dropdown.value = userInput;
    } else {
      dropdown.value = "Sick"; // default fallback
    }

    inputField.value = "";
    inputField.style.display = "none";
    dropdown.style.display = "inline-block";

    dropdown.focus();
  }