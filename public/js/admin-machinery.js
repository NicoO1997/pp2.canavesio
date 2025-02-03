function submitAndReload(machineryId) {
    console.log('Submitting machinery with ID:', machineryId);
    const form = document.getElementById(`machinery-form-${machineryId}`);
    const formData = new FormData(form);

    // Log the form data (be careful with sensitive information)
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json().then(data => ({ status: response.status, data }));
    })
    .then(({ status, data }) => {
        console.log('Response data:', data);
        if (status >= 200 && status < 300) {
            alert('Machinery updated successfully!');
            window.location.reload();
        } else {
            alert(`Error updating machinery: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error updating the machinery.');
    });
}