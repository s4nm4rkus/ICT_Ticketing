function openModal(imageSrc) {
    let modal = document.getElementById("imageModal");
    let modalImg = document.getElementById("modalImage");
    
    modal.style.display = "flex";
    modalImg.src = imageSrc;
}

function closeModal() {
    document.getElementById("imageModal").style.display = "none";
}