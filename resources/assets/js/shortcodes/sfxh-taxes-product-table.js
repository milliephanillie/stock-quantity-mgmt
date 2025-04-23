const sqmgmtState = {
    page: 1,
    totalPages: 1,
};

// const fetchVariations = (page) => {
//     const tableBody = document.getElementById("sqmgmt-product-stock-list");
//     const paginationInfo = document.getElementById("sqmgmt-pagination-info");
//     const prevButton = document.getElementById("otslr-prev");
//     const nextButton = document.getElementById("otslr-next");

//     const postId = window.stockMgmt.post_id;
//     const url = `${window.stockMgmt.siteUrl}/wp-json/stockmgmt/v1/product-stock-list?page=${page}&post_id=${postId}`;

//     fetch(url)
//         .then(response => response.json())
//         .then(data => {
//             if (data.status === "success") {
//                 tableBody.innerHTML = data.data; 
//                 sqmgmtState.page = page;
//                 sqmgmtState.totalPages = parseInt(paginationInfo.dataset.totalPages);

//                 if (sqmgmtState.page <= 1) {
//                     prevButton.disabled = true;
//                     prevButton.classList.add("disabled");
//                 } else {
//                     prevButton.disabled = false;
//                     prevButton.classList.remove("disabled");
//                 }

//                 if (sqmgmtState.page >= sqmgmtState.totalPages) {
//                     nextButton.disabled = true;
//                     nextButton.classList.add("disabled");
//                 } else {
//                     nextButton.disabled = false;
//                     nextButton.classList.remove("disabled");
//                 }
//             }
//         })
//         .catch(error => console.error("Error fetching variations:", error));
// };

const modalOverlayBg = () => {
    return `
        <style>
            .otslr-modal-overlay-bg {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(0, 0, 0, 0.6);
                z-index: 9998;
            }
        </style>
        <div class="otslr-modal-overlay-bg"></div>
    `;
};

const modalHtml = (src) => {
    return `
        <style>
            .otslr-modal-wrapper {
                position: fixed;
                top: 55%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                z-index: 99999;
                max-width: 90%;
                max-height: 90%;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            }

            .otslr-modal-wrapper-inner {
                padding: 16px;
                text-align: center;
            }

            .otslr-modal-wrapper-inner img {
                max-width: 100%;
                max-height: 80vh;
            }

            .otslr-modal-close {
                position: absolute;
                top: 10px;
                right: 16px;
                font-size: 24px;
                font-weight: bold;
                color: #333;
                cursor: pointer;
            }
        </style>
        <div class="otslr-modal-wrapper">
            <div class="otslr-modal-close">×</div>
            <div class="otslr-modal-wrapper-inner">
                <img src="${src}" alt="Drawing">
            </div>
        </div>
    `;
};

const initProductDrawingModal = () => {
    const links = document.querySelectorAll('.otslr-drawing-modal');
    const body = document.body;

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const src = link.getAttribute('data-src') || link.getAttribute('href');

            // Remove any existing modal
            document.querySelector('.otslr-modal-overlay-bg')?.remove();
            document.querySelector('.otslr-modal-wrapper')?.remove();

            // Insert modal
            body.insertAdjacentHTML('beforeend', modalOverlayBg());
            body.insertAdjacentHTML('beforeend', modalHtml(src));

            // Set up close logic
            document.querySelector('.otslr-modal-close')?.addEventListener('click', () => {
                document.querySelector('.otslr-modal-overlay-bg')?.remove();
                document.querySelector('.otslr-modal-wrapper')?.remove();
            });

            document.querySelector('.otslr-modal-overlay-bg')?.addEventListener('click', () => {
                document.querySelector('.otslr-modal-overlay-bg')?.remove();
                document.querySelector('.otslr-modal-wrapper')?.remove();
            });
        });
    });
};

document.addEventListener('DOMContentLoaded', initProductDrawingModal);


const initTableScroll = () => {
    const overflowX = document.querySelector('.otslr-product-stock-list');
    const btnPrev = document.getElementById("otslr-prev");
    const btnNext = document.getElementById("otslr-next");

    const frozen = document.querySelectorAll('.otslr-frozen');

    if (!overflowX) return;

    const applyScrollClass = () => {
        frozen.forEach(el => el.classList.add('otslr-scrolled'));
        
        clearTimeout(overflowX._scrollTimeout);
        overflowX._scrollTimeout = setTimeout(() => {
            frozen.forEach(el => el.classList.remove('otslr-scrolled'));
        }, 1000);
    };

    overflowX.addEventListener('scroll', applyScrollClass);

    if (btnPrev) {
        btnPrev.addEventListener("click", () => {
            overflowX.scrollTo({
                left: 0,
                behavior: 'smooth'
            });
            applyScrollClass();
        });
    }

    if (btnNext) {
        btnNext.addEventListener("click", () => {
            overflowX.scrollTo({
                left: overflowX.scrollWidth,
                behavior: 'smooth'
            });
            applyScrollClass();
        });
    }
};

document.addEventListener("DOMContentLoaded", initTableScroll);


