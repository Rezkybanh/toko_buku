// Function to open modal
function openModal() {
    document.getElementById('myModal').style.display = 'flex';
}

// Function to close modal
function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

// Close the modal when clicking outside the modal content
window.onclick = function(event) {
    if (event.target == document.getElementById('myModal')) {
        closeModal();
    }
}
