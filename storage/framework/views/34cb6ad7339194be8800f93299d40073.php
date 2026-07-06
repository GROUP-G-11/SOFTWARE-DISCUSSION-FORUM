

<?php $__env->startSection('title', 'Gradebook'); ?>

<?php $__env->startSection('content'); ?>
<div class="eyebrow">Grading and Participation</div>
<h1 id="groupName">Loading gradebook…</h1>

<div class="card">
    <table id="gradebookTable">
        <thead>
            <tr>
                <th>Student</th>
                <th>Participation</th>
                <th>Quiz score</th>
                <th># quizzes taken</th>
                <th>Overall total</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const groupId = <?php echo e($group); ?>;

async function loadGradebook() {
    const data = await api(`/groups/${groupId}/gradebook`);
    if (!data || data.message) {
        document.getElementById('groupName').textContent = 'Gradebook';
        document.querySelector('#gradebookTable tbody').innerHTML =
            `<tr><td colspan="5" class="muted">${data?.message ?? 'Could not load the gradebook (are you the lecturer for this group?).'}</td></tr>`;
        return;
    }

    document.getElementById('groupName').textContent = `${data.group} — Gradebook`;

    const tbody = document.querySelector('#gradebookTable tbody');
    tbody.innerHTML = (data.rows || []).map(r => `
        <tr>
            <td>${r.full_name}</td>
            <td>${Number(r.participation_total).toFixed(2)}</td>
            <td>${Number(r.quiz_total).toFixed(2)}</td>
            <td>${r.quiz_attempts_count}</td>
            <td><strong>${Number(r.overall_total).toFixed(2)}</strong></td>
        </tr>
    `).join('') || '<tr><td colspan="5" class="muted">No members with grades yet.</td></tr>';
}

loadGradebook();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\SOFTWARE-DISCUSSION-FORUM-Grading-and-Participation\resources\views/groups/gradebook.blade.php ENDPATH**/ ?>