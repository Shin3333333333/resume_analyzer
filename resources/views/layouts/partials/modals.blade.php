<!-- SHARE MODAL -->
<div class="modal-backdrop" id="share-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="share-modal-title">↗ Share to Community</div>
      <button class="modal-close" onclick="closeModal('share-modal')">✕</button>
    </div>
    <div id="share-modal-body"></div>
  </div>
</div>

<!-- ENTRY TYPE MODAL -->
<div class="modal-backdrop" id="entry-type-modal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header"><div class="modal-title">What would you like to create?</div><button class="modal-close" onclick="closeModal('entry-type-modal')">✕</button></div>
    <div style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
      <button class="btn btn-primary" style="padding:16px;font-size:1rem;justify-content:center" onclick="closeModal('entry-type-modal');openJournalModal()">📓 Journal Entry</button>
      <button class="btn btn-primary" style="padding:16px;font-size:1rem;justify-content:center" onclick="closeModal('entry-type-modal');openTaskModal()">✅ Task</button>
      <button class="btn btn-primary" style="padding:16px;font-size:1rem;justify-content:center" onclick="closeModal('entry-type-modal');openGoalModal()">🎯 Goal</button>
    </div>
  </div>
</div>

<!-- JOURNAL MODAL -->
<div class="modal-backdrop" id="journal-modal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title" id="jmodal-title">📓 New Journal Entry</div><button class="modal-close" onclick="closeModal('journal-modal')">✕</button></div>
    <div class="form-group"><label class="form-label">Title (optional)</label><input type="text" class="form-input" id="journal-title" placeholder="Give this moment a title..."></div>
    <div class="form-group">
      <label class="form-label">How are you feeling?</label>
      <div class="mood-picker" id="mood-picker">
        <div class="mood-option" data-mood="5" onclick="selectMood(5,'😄')">😄<span>Great</span></div>
        <div class="mood-option" data-mood="4" onclick="selectMood(4,'🙂')">🙂<span>Good</span></div>
        <div class="mood-option" data-mood="3" onclick="selectMood(3,'😐')">😐<span>Okay</span></div>
        <div class="mood-option" data-mood="2" onclick="selectMood(2,'😔')">😔<span>Low</span></div>
        <div class="mood-option" data-mood="1" onclick="selectMood(1,'😢')">😢<span>Rough</span></div>
      </div>
    </div>
    <div class="form-group"><label class="form-label">Your thoughts</label><textarea class="form-textarea" id="journal-content" style="min-height:160px" placeholder="What's on your mind?"></textarea></div>
    <div class="form-group"><label class="form-label">Tags (comma-separated)</label><input type="text" class="form-input" id="journal-tags" placeholder="gratitude, work, family..."></div>
    <div class="form-group">
      <label class="form-label">Photos (optional — max 6)</label>
      <input type="file" id="journal-photos" multiple accept="image/*" style="display:block;color:var(--muted);font-size:.8rem">
      <div id="photo-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"></div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
      <button class="btn" onclick="closeModal('journal-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveJournalEntry()">Save Entry</button>
    </div>
  </div>
</div>

<!-- TASK MODAL -->
<div class="modal-backdrop" id="task-modal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title" id="tmodal-title">✅ Add Task</div><button class="modal-close" onclick="closeModal('task-modal')">✕</button></div>
    <div class="form-group"><label class="form-label">Task</label><input type="text" class="form-input" id="task-text" placeholder="What needs to be done?"></div>
    <div class="form-group"><label class="form-label">Priority</label><select class="form-select" id="task-priority"><option value="high">🔴 High</option><option value="med" selected>🟡 Medium</option><option value="low">🟢 Low</option></select></div>
    <div class="form-group"><label class="form-label">Note (optional)</label><input type="text" class="form-input" id="task-note" placeholder="Any details..."></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
      <button class="btn" onclick="closeModal('task-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveTask()">Save Task</button>
    </div>
  </div>
</div>

<!-- GOAL MODAL -->
<div class="modal-backdrop" id="goal-modal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title" id="gmodal-title">🎯 New Goal</div><button class="modal-close" onclick="closeModal('goal-modal')">✕</button></div>
    <div class="form-group"><label class="form-label">Goal Name</label><input type="text" class="form-input" id="goal-name" placeholder="What do you want to achieve?"></div>
    <div class="form-group"><label class="form-label">Category</label><select class="form-select" id="goal-category"><option value="health">💪 Health &amp; Fitness</option><option value="learn">📚 Learning</option><option value="finance">💰 Finance</option><option value="career">💼 Career</option><option value="personal" selected>🌱 Personal Growth</option><option value="other">⭐ Other</option></select></div>
    <div class="form-group"><label class="form-label">Target</label><input type="text" class="form-input" id="goal-target" placeholder="e.g. Read 12 books, Run 5km..."></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
      <button class="btn" onclick="closeModal('goal-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveGoal()">Create Goal</button>
    </div>
  </div>
</div>

<!-- EDIT PROFILE MODAL -->
<div class="modal-backdrop" id="edit-profile-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-header"><div class="modal-title">✎ Edit Profile</div><button class="modal-close" onclick="closeModal('edit-profile-modal')">✕</button></div>
    <div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-input" id="edit-fullname" placeholder="Your full name..."></div>
    <div class="form-group">
      <label class="form-label">Username</label>
      <div style="position:relative"><span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-family:'JetBrains Mono',monospace;font-size:.85rem">@</span><input type="text" class="form-input" id="edit-username" placeholder="yourhandle" style="padding-left:28px"></div>
      <div style="font-size:.68rem;color:var(--muted);margin-top:5px;font-family:'JetBrains Mono',monospace">Lowercase letters, numbers, underscores only</div>
    </div>
    <div class="form-group"><label class="form-label">Bio</label><textarea class="form-textarea" id="edit-bio" placeholder="Tell the community a bit about yourself…" style="min-height:90px"></textarea></div>
    <div class="form-group"><label class="form-label">Location <span style="opacity:.5">(optional)</span></label><input type="text" class="form-input" id="edit-location" placeholder="City, Country..."></div>
    <div class="form-group"><label class="form-label">Website <span style="opacity:.5">(optional)</span></label><input type="text" class="form-input" id="edit-website" placeholder="https://..."></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
      <button class="btn" onclick="closeModal('edit-profile-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveProfile()">Save Profile</button>
    </div>
  </div>
</div>

<!-- AVATAR MODAL -->
<div class="modal-backdrop" id="avatar-modal">
  <div class="modal" style="max-width:480px">
    <div class="modal-header"><div class="modal-title">🖼 Change Avatar</div><button class="modal-close" onclick="closeModal('avatar-modal')">✕</button></div>
    <div class="form-group">
      <label class="form-label">Upload your own</label>
      <input type="file" id="avatar-upload" accept="image/*" style="color:var(--muted);font-size:.8rem;display:block">
      <div style="font-size:.68rem;color:var(--muted);margin-top:4px;font-family:'JetBrains Mono',monospace">Auto-compressed to ~80KB</div>
    </div>
    <div class="form-group"><label class="form-label">Or pick a preset</label><div class="avatar-picker-grid" id="avatar-preset-grid"></div></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
      <button class="btn" onclick="closeModal('avatar-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveAvatar()">Apply Avatar</button>
    </div>
  </div>
</div>

<!-- COVER MODAL — with hex picker -->
<div class="modal-backdrop" id="cover-modal">
  <div class="modal" style="max-width:520px">
    <div class="modal-header"><div class="modal-title">🖼 Change Cover</div><button class="modal-close" onclick="closeModal('cover-modal')">✕</button></div>

    <div class="form-group">
      <label class="form-label">Preset Gradients</label>
      <div class="cover-preset-grid" id="cover-preset-grid"></div>
    </div>

    <div class="hex-input-section">
      <label class="form-label" style="display:block;margin-bottom:10px">🎨 Custom Color — enter a hex code to auto-generate a gradient</label>
      <div class="hex-input-row">
        <input type="color" class="hex-color-input" id="hex-color-wheel" title="Pick a color">
        <input type="text" class="hex-text-input" id="hex-text-input" placeholder="#4f8ef7" maxlength="7">
        <button class="hex-apply-btn" onclick="applyHexCover()">Apply ✓</button>
      </div>
      <div class="cover-live-preview" id="cover-live-preview">
        <span class="cover-live-preview-label">Live Preview</span>
      </div>
      <div style="font-size:.65rem;color:var(--muted);font-family:'JetBrains Mono',monospace;margin-top:8px;opacity:.7">The gradient is auto-generated: your color is blended with dark tones to keep the cover elegant.</div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
      <button class="btn" onclick="closeModal('cover-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveCover()">Apply Cover</button>
    </div>
  </div>
</div>


<!-- Journal Expand Overlay -->
<div id="journal-expand-overlay" class="journal-expand-overlay">
    <div class="expanded-card">
        <div class="expanded-card-header">
            <h2 id="expanded-card-title"></h2>
            <div class="header-actions">
                <button id="expanded-edit-btn" class="icon-btn">✏️</button>
                <button id="expanded-del-btn" class="icon-btn">🗑️</button>
                <button class="modal-close" onclick="closeJournalExpand()">×</button>
            </div>
        </div>
        <div class="expanded-card-meta">
            <span id="expanded-card-date"></span>
            <span id="expanded-mood-badge" class="mood-badge"></span>
        </div>
        <div id="expanded-card-content" class="expanded-card-content"></div>
        <div id="expanded-photos" class="expanded-photos"></div>
        <div id="expanded-tags" class="expanded-tags"></div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>