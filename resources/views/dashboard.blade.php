<div id="page-dashboard" class="page active">
      <div class="page-header">
        <div>
          <div class="page-title">Good <span id="greeting-time">day</span> ✦</div>
          <div class="page-subtitle" id="today-date"></div>
          <div style="font-size:.9rem;color:var(--muted);font-family:'Newsreader',serif;font-style:italic;margin-top:6px" id="daily-motivation"></div>
        </div>
        <button class="btn btn-primary" onclick="openEntryTypeModal()">+ New Entry</button>
      </div>
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Journal Entries</div><div class="stat-value" id="stat-entries" style="color:var(--accent)">0</div><div class="stat-change">All time</div></div>
        <div class="stat-card"><div class="stat-label">Tasks Done</div><div class="stat-value" id="stat-tasks" style="color:var(--green)">0</div><div class="stat-change">This week</div></div>
        <div class="stat-card"><div class="stat-label">Active Goals</div><div class="stat-value" id="stat-goals" style="color:var(--lavender)">0</div><div class="stat-change">In progress</div></div>
        <div class="stat-card"><div class="stat-label">Streak</div><div class="stat-value" id="stat-streak" style="color:var(--amber)">0</div><div class="stat-change">days 🔥</div></div>
        <div class="stat-card"><div class="stat-label">Fav Mood</div><div class="stat-value" id="stat-mood" style="font-size:2rem">—</div><div class="stat-change">Most common</div></div>
      </div>
      <div style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn" onclick="exportAsJSON()">💾 Export Backup</button>
        <button class="btn" onclick="navigateTo('community')" style="border-color:rgba(45,212,191,.3);color:var(--teal)">🌐 Community Feed</button>
      </div>
      <div class="grid-2">
        <div class="card">
          <div class="card-title">📓 Recent Entries <span style="font-family:'JetBrains Mono',monospace;font-size:.58rem;color:var(--muted);font-weight:400;margin-left:auto">↗ click to expand</span></div>
          <div id="dash-journal-list"></div>
        </div>
        <div class="card"><div class="card-title">✅ Today's Tasks</div><div id="dash-task-list"></div></div>
      </div>
      <div class="card"><div class="card-title">🎯 Goals Progress <button class="btn-sm" style="margin-left:auto" onclick="navigateTo('goals')">View all</button></div><div id="dash-goals-list"></div></div>
    </div>