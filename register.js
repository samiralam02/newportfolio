document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var form = this;
    var formData = new FormData(form);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'register.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                document.getElementById('successMessage').innerText = response.success;
                document.getElementById('successMessage').style.display = 'block';
                document.getElementById('errorMessages').innerText = '';
                form.reset();
            } else {
                document.getElementById('errorMessages').innerText = response.error;
                document.getElementById('successMessage').style.display = 'none';
            }
        }
    };
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
