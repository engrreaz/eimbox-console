<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TinyMCE Test</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.8.0/tinymce.min.js"></script>
</head>
<body>
<textarea id="editor">Hello world</textarea>

<script>
document.addEventListener("DOMContentLoaded", function() {
    tinymce.init({
        selector: '#editor',
        height: 300,
        menubar: true,
        plugins: 'lists link image code',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link image | code'
    });
});
</script>
</body>
</html>
