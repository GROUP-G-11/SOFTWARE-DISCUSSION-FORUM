@extends('layouts.app')

@section('title', 'Topic')

@section('content')
<div class="eyebrow" id="topicCategory">Topic</div>
<h1 id="topicTitle">Loading…</h1>
<a class="btn secondary" id="exportLink" href="#" target="_blank">Export to PDF</a>

<div class="card">
    <h3>Write a post</h3>
    <form id="postForm">
        <textarea id="postContent" rows="3" placeholder="Share your thoughts…" required></textarea>

        <label for="excludeGroup" class="muted" style="display:block; margin-top:8px;">Exclude members of group (optional)</label>
        <select id="excludeGroup" style="margin-bottom:6px;">
            <option value="">— Select a group —</option>
        </select>

        <label for="excludeUsers" class="muted" style="display:block;">Exclude specific users (optional)</label>
        <select id="excludeUsers" multiple style="min-height:100px; margin-bottom:10px;">
            <option disabled>Select a group above to load its members…</option>
        </select>

        <button class="btn" type="submit">Post</button>
    </form>
</div>

<div id="posts"></div>
@endsection

@section('scripts')
<script>
const topicId = {{ $topic }};

async function loadTopic() {
    const t = await api(`/topics/${topicId}`);
    document.getElementById('topicTitle').textContent = t.title;
    document.getElementById('topicCategory').textContent = t.category ?? 'General';
    document.getElementById('exportLink').href = `/api/topics/${topicId}/export`;
    renderPosts(t.posts || []);
}

/** Populate the "exclude by group" dropdown from the groups that actually exist. */
async function loadGroupsForExclusion() {
    const select = document.getElementById('excludeGroup');
    try {
        const res = await api('/groups');
        const groups = res.data || res; // handles paginated or plain array response
        select.innerHTML = '<option value="">— Select a group —</option>' +
            groups.map(g => `<option value="${g.group_id}">${g.name}</option>`).join('');
    } catch (err) {
        console.error('Could not load groups', err);
    }
}

/** When a group is picked, load its real members into the exclude-users multi-select. */
async function loadMembersForExclusion(groupId) {
    const select = document.getElementById('excludeUsers');
    if (!groupId) {
        select.innerHTML = '<option disabled>Select a group above to load its members…</option>';
        return;
    }
    select.innerHTML = '<option disabled>Loading members…</option>';
    try {
        const res = await api(`/groups/${groupId}/members`);
        const members = res.data || res;
        select.innerHTML = members.length
            ? members.map(m => `<option value="${m.user_id}">${m.full_name}</option>`).join('')
            : '<option disabled>No members in this group.</option>';
    } catch (err) {
        console.error('Could not load group members', err);
        select.innerHTML = '<option disabled>Failed to load members.</option>';
    }
}

document.getElementById('excludeGroup').addEventListener('change', (e) => {
    loadMembersForExclusion(e.target.value);
});

function renderPosts(posts) {
    document.getElementById('posts').innerHTML = posts.map(p => `
        <div class="card">
            <strong>${p.author.full_name}</strong>
            <span class="muted">${new Date(p.posted_at).toLocaleString()}</span>
            ${p.is_flagged ? '<span class="flag"> · flagged</span>' : ''}
            <p>${p.content}</p>
            <button class="btn secondary" onclick="shareToSocial(${p.post_id})">Forward</button>
            <button class="btn secondary" onclick="flagPost(${p.post_id})">Flag</button>
            <div style="margin-top:10px; padding-left:16px; border-left: 2px solid #d8d2c4;">
                ${(p.replies || []).map(r => `
                    <div style="margin-bottom:8px;">
                        <strong>${r.author.full_name}</strong>
                        <span class="muted">${new Date(r.replied_at).toLocaleString()}</span>
                        <div>${r.content}</div>
                    </div>
                `).join('')}
                <form onsubmit="return submitReply(event, ${p.post_id})">
                    <input type="text" placeholder="Reply…" required>
                    <button class="btn secondary" type="submit">Reply</button>
                </form>
            </div>
        </div>
    `).join('') || '<div class="muted">No posts yet in this topic.</div>';
}

async function submitReply(e, postId) {
    e.preventDefault();
    const input = e.target.querySelector('input');
    await api(`/posts/${postId}/replies`, { method: 'POST', body: { content: input.value } });
    loadTopic();
    return false;
}

async function shareToSocial(postId) {
    await api(`/posts/${postId}/share`, { method: 'POST', body: { platform: 'Clipboard' } });
    alert('Link copied and share logged.');
}

async function flagPost(postId) {
    await api(`/posts/${postId}/flag`, { method: 'POST' });
    loadTopic();
}

document.getElementById('postForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const exclude_user_ids = Array.from(document.getElementById('excludeUsers').selectedOptions)
        .map(opt => parseInt(opt.value))
        .filter(id => !isNaN(id));
    await api(`/topics/${topicId}/posts`, {
        method: 'POST',
        body: { content: document.getElementById('postContent').value, exclude_user_ids },
    });
    e.target.reset();
    document.getElementById('excludeGroup').value = '';
    loadMembersForExclusion(null);
    loadTopic();
});

loadTopic();
loadGroupsForExclusion();
</script>
@endsection
