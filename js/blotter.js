function nextStep() {
    const page1 = document.getElementById('Page_1');
    const requiredFields = page1.querySelectorAll(':required');
    let allValid = true;

    requiredFields.forEach(field => {
        if (!field.checkValidity()) {
            field.reportValidity();
            allValid = false;
        }
    });

    if (allValid) {
        document.getElementById('Page_1').style.display = 'none';
        document.getElementById('Page_2').style.display = 'block';
        document.querySelector('.main-content, main').scrollTop = 0; // Scroll to top
    }
}

function prevStep() {
    document.getElementById('Page_1').style.display = 'block';
    document.getElementById('Page_2').style.display = 'none';
    document.querySelector('.main-content, main').scrollTop = 0; // Scroll to top
}

document.getElementById('blotterForm').addEventListener('submit', function(e) {
    const acknowledgment = document.getElementById('acknowledgment');
    if (!acknowledgment.checked) {
        e.preventDefault();
        alert('Please acknowledge the terms before submitting.');
        acknowledgment.focus();
    }
});