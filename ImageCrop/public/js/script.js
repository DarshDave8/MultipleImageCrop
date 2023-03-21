const fileInputs = document.querySelectorAll('.file-input');

fileInputs.forEach((fileInput) => {
  const file = fileInput.files[0];

  const reader = new FileReader();
  reader.onload = function() {
    const base64 = reader.result.split(',')[1];
    console.log(base64);
    // or do something else with the base64 string
  };
  reader.readAsDataURL(file);
});
