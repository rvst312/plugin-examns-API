/*=================================================
*  RESULTS
*================================================*/

// Charge skeleton loader or show results
document.addEventListener('DOMContentLoaded', function() {
    const skeletonContainer = document.getElementById('skeleton-container');
    const resultsContainer = document.getElementById('results-container');
    const images = resultsContainer.getElementsByTagName('img');
    let loadedImages = 0;

    function showResults() {
        skeletonContainer.classList.add('hidden');
        resultsContainer.classList.remove('hidden');
    }

    if (images.length === 0) {
        setTimeout(showResults, 500);
    } else {
        Array.from(images).forEach(img => {
            if (img.complete) {
                loadedImages++;
                if (loadedImages === images.length) {
                    showResults();
                }
            } else {
                img.addEventListener('load', () => {
                    loadedImages++;
                    if (loadedImages === images.length) {
                        showResults();
                    }
                });
            }
        });
        setTimeout(showResults, 1500);
    }
});