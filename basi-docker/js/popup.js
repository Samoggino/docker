function openClosePopup() {
    var popup = document.getElementById("myPopup");

    if (popup.style.display === "none" || popup.style.display === "") {
        popup.style.display = "flex";
    } else {
        popup.style.display = "none";
    }
}

document.getElementById("popup-btn").addEventListener("click", openPopup);