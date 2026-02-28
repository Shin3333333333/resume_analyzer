 <!-- COMMUNITY -->
    <div id="page-community" class="page">
      <div class="page-header">
        <div><div class="page-title">Community 🌐</div><div class="page-subtitle">Share your journey · inspire others · grow together</div></div>
        <button class="btn" onclick="openShareModal()" style="border-color:rgba(45,212,191,.3);color:var(--teal)">↗ Share Something</button>
      </div>
      <div class="community-stats">
        <div class="comm-stat"><div class="comm-stat-val" id="comm-stat-posts" style="color:var(--accent)">—</div><div class="comm-stat-label">Total Posts</div></div>
        <div class="comm-stat"><div class="comm-stat-val" id="comm-stat-members" style="color:var(--teal)">—</div><div class="comm-stat-label">Members</div></div>
        <div class="comm-stat"><div class="comm-stat-val" id="comm-stat-likes" style="color:var(--rose)">—</div><div class="comm-stat-label">Likes Given</div></div>
      </div>
      <div class="feed-container">
        <div class="feed-composer">
          <div class="composer-top">
            <img class="composer-avatar" id="composer-avatar" src="" alt="">
            <textarea class="composer-input" id="composer-text" placeholder="Share a thought, update, or inspiration…" rows="3"></textarea>
          </div>
          <div class="composer-bottom">
            <div class="composer-actions">
              <button class="composer-type-btn active" data-type="thought" onclick="setComposerType('thought')">💭 Thought</button>
              <button class="composer-type-btn" data-type="journal" onclick="openShareModal('journal')">📓 Journal</button>
              <button class="composer-type-btn" data-type="task" onclick="openShareModal('task')">✅ Task</button>
              <button class="composer-type-btn" data-type="goal" onclick="openShareModal('goal')">🎯 Goal</button>
            </div>
            <button class="btn btn-primary" onclick="postThought()" style="padding:8px 18px">Post</button>
          </div>
        </div>
        <div class="feed-filters">
          <button class="feed-filter-btn active" onclick="filterFeed('all',this)">All</button>
          <button class="feed-filter-btn" onclick="filterFeed('thought',this)">💭 Thoughts</button>
          <button class="feed-filter-btn" onclick="filterFeed('journal',this)">📓 Journals</button>
          <button class="feed-filter-btn" onclick="filterFeed('task',this)">✅ Tasks</button>
          <button class="feed-filter-btn" onclick="filterFeed('goal',this)">🎯 Goals</button>
          <button class="feed-filter-btn" onclick="filterFeed('mine',this)">👤 Mine</button>
        </div>
        <div id="feed-list"><div class="loading-posts">Loading community posts…</div></div>
        <div id="post-template" style="display: none;">
             <div class="post-actions">
               <button onclick="toggleLike('__POST_ID__')">Like</button>
               <button onclick="loadComments('__POST_ID__')">Comment</button>
             </div>
             <div id="comments-__POST_ID__" class="comments-section"></div>
             <div class="comment-form">
               <input type="text" id="comment-input-__POST_ID__" placeholder="Add a comment...">
               <button onclick="addComment('__POST_ID__')">Post</button>
             </div>
         </div>
      </div>
    </div>