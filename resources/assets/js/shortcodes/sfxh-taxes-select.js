
 const initVariationSelect = () => {
    const selectElement = document.querySelector(".otslr-nice-select");

    if (selectElement) {
        selectElement.addEventListener("change", function () {
            let selectedSku = this.value;

            document.querySelectorAll(".otslr-variation-details").forEach(function (el) {
                el.style.display = "none";
            });

            let selectedVariation = document.querySelector(".otslr-" + selectedSku);
            if (selectedVariation) {
                selectedVariation.style.display = "block";
            }
        });
    }
}

const makeAlert = (message) => {
    alert(message)
}

(function() {
    initVariationSelect()
})();

