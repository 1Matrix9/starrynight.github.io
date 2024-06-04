// Add event listener to the "Bid Now!" buttons
const bidNowButtons = document.querySelectorAll('.productdetail a');

bidNowButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        // Prevent default button behavior
        event.preventDefault();

        // Get the product ID from the button's data attribute
        const productId = button.dataset.productId;

        // Simulate a server request to update the product quantity
        fetch(`https://example.com/update-product-quantity?productId=${productId}`)
            .then((response) => response.json())
            .then((data) => {
                // Update the product quantity in the UI (e.g., display the new quantity)
                console.log(`Product quantity updated: ${data.quantity}`);
            })
            .catch((error) => {
                console.error('Error updating product quantity:', error);
            });
    });
});
