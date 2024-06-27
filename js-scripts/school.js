document.querySelectorAll('input[name="action"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const schoolFormFields = document.getElementById('school_form_fields');
        const existingSchoolSelect = document.getElementById('existing_school_select');

        if (this.value === 'create') {
            schoolFormFields.style.display = 'block';
            existingSchoolSelect.style.display = 'none';
            clearFormFields();
        } else if (this.value === 'update' || this.value === 'delete') {
            schoolFormFields.style.display = this.value === 'update' ? 'block' : 'none';
            existingSchoolSelect.style.display = 'block';
        }
    });
});

document.getElementById('school_id').addEventListener('change', function() {
    const schoolId = this.value;
    if (!schoolId) {
        clearFormFields();
        return;
    }

    fetch(`http://localhost/grade-center/controllers/get_single_school.php?school_id=${schoolId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('name').value = data.name;
            document.getElementById('country').value = data.country;
            document.getElementById('city').value = data.city;
            document.getElementById('street').value = data.street;
            document.getElementById('street_num').value = data.street_num;
            document.getElementById('director_id').value = data.director_id;
        })
        .catch(error => console.error('Error fetching school data:', error));
});

function clearFormFields() {
    document.getElementById('name').value = '';
    document.getElementById('country').value = '';
    document.getElementById('city').value = '';
    document.getElementById('street').value = '';
    document.getElementById('street_num').value = '';
    document.getElementById('director_id').value = '';
}