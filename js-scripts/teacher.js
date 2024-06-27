document.querySelectorAll('input[name="operation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'create') {
            document.getElementById('existing_grade_select').style.display = 'none';
            document.getElementById('grade').disabled = false;
            document.getElementById('student_id').disabled = false;
            document.getElementById('subject_id').disabled = false;
            clearFormFields();
        } else {
            document.getElementById('existing_grade_select').style.display = 'block';
            if (this.value === 'delete') {
                document.getElementById('grade').disabled = true;
                document.getElementById('student_id').disabled = true;
                document.getElementById('subject_id').disabled = true;
            } else {
                document.getElementById('grade').disabled = false;
                document.getElementById('student_id').disabled = false;
                document.getElementById('subject_id').disabled = false;
            }
        }
    });
});

document.getElementById('grade_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('grade').value = selectedOption.getAttribute('data-grade');
    document.getElementById('student_id').value = selectedOption.getAttribute('data-student');
    document.getElementById('subject_id').value = selectedOption.getAttribute('data-subject');
});

function clearFormFields() {
    document.getElementById('grade').value = '';
    document.getElementById('student_id').value = '';
    document.getElementById('subject_id').value = '';
    document.getElementById('grade_id').selectedIndex = -1;
}