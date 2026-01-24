(() => {
  const dropzone = document.getElementById('photoDropzone');
  const input = document.getElementById('photoInput');
  const filename = document.getElementById('photoFilename');
  if (!dropzone || !input) return;

  const updateName = (file) => {
    if (filename) {
      filename.textContent = file ? file.name : 'No file selected';
    }
  };

  input.addEventListener('change', () => {
    updateName(input.files && input.files[0] ? input.files[0] : null);
  });

  dropzone.addEventListener('dragover', (event) => {
    event.preventDefault();
    dropzone.classList.add('dragover');
  });

  dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
  });

  dropzone.addEventListener('drop', (event) => {
    event.preventDefault();
    dropzone.classList.remove('dragover');
    const file = event.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    updateName(file);
  });
})();
