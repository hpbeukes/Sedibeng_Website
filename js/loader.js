function loadText(file, elementId) {
    fetch(file)
        .then(response => response.text())
        .then(data => {
            document.getElementById(elementId).innerHTML = data.replace(/\n/g, "<br>");
        })
        .catch(error => {
            document.getElementById(elementId).innerHTML = "Content not available.";
        });
}
