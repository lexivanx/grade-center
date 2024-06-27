document.querySelectorAll('input[name="operation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'create') {
            document.getElementById('existing_relationship_select').style.display = 'none';
            document.getElementById('parent_id').disabled = false;
            document.getElementById('child_id').disabled = false;
            clearFormFields();
        } else {
            document.getElementById('existing_relationship_select').style.display = 'block';
            if (this.value === 'delete') {
                document.getElementById('parent_id').disabled = true;
                document.getElementById('child_id').disabled = true;
            } else {
                document.getElementById('parent_id').disabled = false;
                document.getElementById('child_id').disabled = false;
            }
        }
    });
});

document.getElementById('parent_child_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('parent_id').value = selectedOption.getAttribute('data-parent');
    document.getElementById('child_id').value = selectedOption.getAttribute('data-child');
});

function clearFormFields() {
    document.getElementById('parent_id').value = '';
    document.getElementById('child_id').value = '';
    document.getElementById('parent_child_id').selectedIndex = -1;
}