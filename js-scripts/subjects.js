document.querySelectorAll('input[name="operation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'create') {
            document.getElementById('existing_subject_select').style.display = 'none';
            document.getElementById('name').disabled = false;
            clearFormFields();
        } else {
            document.getElementById('existing_subject_select').style.display = 'block';
            if (this.value === 'delete') {
                document.getElementById('name').disabled = true;
            } else {
                document.getElementById('name').disabled = false;
            }
        }
    });
});

document.getElementById('subject_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('name').value = selectedOption.getAttribute('data-name');
});

function clearFormFields() {
    document.getElementById('name').value = '';
    document.getElementById('subject_id').selectedIndex = -1;
}