document.querySelectorAll('input[name="operation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'create') {
            document.getElementById('existing_relationship_select').style.display = 'none';
            document.getElementById('teacher_id').disabled = false;
            document.getElementById('subject_id').disabled = false;
            clearFormFields();
        } else {
            document.getElementById('existing_relationship_select').style.display = 'block';
            if (this.value === 'delete') {
                document.getElementById('teacher_id').disabled = true;
                document.getElementById('subject_id').disabled = true;
            } else {
                document.getElementById('teacher_id').disabled = false;
                document.getElementById('subject_id').disabled = false;
            }
        }
    });
});

document.getElementById('teacher_subject_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('teacher_id').value = selectedOption.getAttribute('data-teacher');
    document.getElementById('subject_id').value = selectedOption.getAttribute('data-subject');
});

function clearFormFields() {
    document.getElementById('teacher_id').value = '';
    document.getElementById('subject_id').value = '';
    document.getElementById('teacher_subject_id').selectedIndex = -1;
}