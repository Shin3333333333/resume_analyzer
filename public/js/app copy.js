
// Firebase SDK
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getAuth, GoogleAuthProvider, signInWithPopup, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";
import { getFirestore, collection, getDocs, doc, setDoc, addDoc, deleteDoc, updateDoc, query, orderBy, Timestamp, getDoc, arrayUnion, arrayRemove, limit } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore.js";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyCXl_R3gnGX_rxfjy6rNi_JdJNgdbNVTg0",
  authDomain: "resume-analyzer-2d4f2.firebaseapp.com",
  projectId: "resume-analyzer-2d4f2",
  storageBucket: "resume-analyzer-2d4f2.firebasestorage.app",
  messagingSenderId: "1029896274597",
  appId: "1:1029896274597:web:b73c1d51c445364f67434c",
  measurementId: "G-XZKS65TXKG"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);
const provider = new GoogleAuthProvider();

let currentUser, journals = [], tasks = [], goals = [];
let editJournalId = null, editTaskId = null, editGoalId = null;
let moodVal = 3, moodEmoji = '😐', photoUrls = [];
let draggedId = null;

const M_EMOJI = {5:'😄',4:'🙂',3:'😐',2:'😟',1:'😭'};
const CAT_ICONS = {personal:'🧘',work:'💼',health:'❤️‍🩹',finance:'💰',learning:'📚'};
const COLORS = ['var(--accent)','var(--green)','var(--amber)','var(--rose)','var(--lavender)','var(--teal)'];

/* ══ AUTH ════════════════════════════════════════════════════ */
window.signIn = () => signInWithPopup(auth, provider).catch(e => toast('Sign-in error: '+e.message,'❌'));
window.signOutUser = () => signOut(auth).catch(e => toast('Sign-out error: '+e.message,'❌'));

document.addEventListener('DOMContentLoaded', () => {
  onAuthStateChanged(auth, user => {
    const loading = document.getElementById('loading');
    const authSc = document.getElementById('auth-screen');
    const appEl = document.getElementById('app');
    if (user) {
      currentUser = user;
      authSc.style.display = 'none';
      appEl.style.display = 'block';
      loading.classList.add('hidden');
      document.getElementById('user-name').textContent = user.displayName;
      document.getElementById('user-email').textContent = user.email;
      document.getElementById('user-avatar').src = user.photoURL;
      loadUserData();
      loadCommunityFeed();
    } else {
      currentUser = null;
      loading.classList.add('hidden');
      authSc.style.display = 'flex';
      appEl.style.display = 'none';
    }
  });
});

async function loadUserData() {
  try {
    const j = await getDocs(query(collection(db,'users',currentUser.uid,'journals'), orderBy('createdAt','desc')));
    journals = j.docs.map(d => ({id:d.id,...d.data(), createdAt:d.data().createdAt.toDate()}));
    const t = await getDocs(query(collection(db,'users',currentUser.uid,'tasks'), orderBy('createdAt','desc')));
    tasks = t.docs.map(d => ({id:d.id,...d.data(), createdAt:d.data().createdAt.toDate()}));
    const g = await getDocs(query(collection(db,'users',currentUser.uid,'goals'), orderBy('createdAt','desc')));
    goals = g.docs.map(d => ({id:d.id,...d.data(), createdAt:d.data().createdAt.toDate()}));
    renderAll();
  } catch (e) {
    toast('Error loading data: '+e.message,'❌');
    console.error("Error loading data:", e);
  }
}

/* ══ UI & HELPERS ════════════════════════════════════════════ */
window.addEventListener('load', () => {
  document.getElementById('loading').classList.add('hidden');
});

window.navigateTo = pageId => {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const newPage = document.getElementById('page-' + pageId);
  if (newPage) {
    newPage.classList.add('active');
  }

  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const newLink = document.querySelector(`.nav-item[onclick="navigateTo('${pageId}')"]`);
  if (newLink) {
    newLink.classList.add('active');
  }

  closeSidebar();
};

window.toast = (msg, icon = '🔔') => {
  const cont = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = 'toast';
  t.innerHTML = `<span>${icon}</span> ${msg}`;
  cont.appendChild(t);
  setTimeout(() => { t.classList.add('out'); t.addEventListener('animationend', () => t.remove()); }, 3500);
};

window.closeModal = id => document.getElementById(id).classList.remove('open');
const esc = s => s.replace(/</g,'&lt;').replace(/>/g,'&gt;');

const sidebar = document.querySelector('.sidebar');
const hamburger = document.querySelector('.hamburger');
const sidebarOverlay = document.querySelector('.sidebar-overlay');
hamburger.addEventListener('click', () => {
  sidebar.classList.add('open');
  sidebarOverlay.classList.add('open');
  hamburger.classList.add('open');
});
document.querySelector('.sidebar-close-btn').addEventListener('click', closeSidebar);
sidebarOverlay.addEventListener('click', closeSidebar);
function closeSidebar() {
  sidebar.classList.remove('open');
  sidebarOverlay.classList.remove('open');
  hamburger.classList.remove('open');
}

/* ══ JOURNAL ═════════════════════════════════════════════════ */
function renderJournals(containerId, limit = null, isDash = false) {
  const container = document.getElementById(containerId);
  if (!container) return;
  let list = journals;
  if (limit) list = list.slice(0, limit);

  if (!list.length) {
    container.innerHTML = `<div class="empty-state">
      <div class="empty-icon">📓</div>
      <div class="empty-text">No journal entries yet. Time to write your first one!</div>
      <button class="btn btn-primary" onclick="openJournalModal()">Create First Entry</button>
    </div>`;
    return;
  }

  container.innerHTML = list.map(e => {
    const date = new Date(e.createdAt);
    const dateStr = `${date.toLocaleString('default',{month:'short'})} ${date.getDate()}, ${date.getFullYear()}`;
    const contentPreview = e.content.substring(0, 120) + (e.content.length > 120 ? '...' : '');
    return `
    <div class="journal-entry" onclick="expandJournal('${e.id}')">
      <div class="entry-header">
        <div class="entry-date">${dateStr}</div>
        <div class="mood-emoji">${e.moodEmoji || '😐'}</div>
      </div>
      <div style="font-weight:600;font-size:.95rem;margin-bottom:6px">${esc(e.title)}</div>
      <div class="entry-preview">${esc(contentPreview)}</div>
      ${e.tags && e.tags.length ? `<div class="entry-tags">${e.tags.map(t => `<span class="tag" style="background:${COLORS[t.length%COLORS.length]}30;color:${COLORS[t.length%COLORS.length]}">${esc(t)}</span>`).join('')}</div>` : ''}
      ${isDash ? `<div class="entry-expand-hint">Click to expand...</div>` : ''}
    </div>`;
  }).join('');
}

window.expandJournal = id => {
  const e = journals.find(j => j.id === id); if (!e) return;
  const date = new Date(e.createdAt);
  document.getElementById('expanded-card-title').textContent = e.title;
  document.getElementById('expanded-card-date').textContent = `${date.toLocaleString('default',{month:'long'})} ${date.getDate()}, ${date.getFullYear()}`;
  document.getElementById('expanded-mood-badge').textContent = e.moodEmoji || '😐';
  document.getElementById('expanded-card-content').textContent = e.content;
  document.getElementById('expanded-photos').innerHTML = (e.photoUrls||[]).map(u => `<img src="${u}" class="expanded-photo" onclick="viewPhoto('${u}')">`).join('');
  document.getElementById('expanded-tags').innerHTML = (e.tags||[]).map(t => `<span class="tag" style="background:${COLORS[t.length%COLORS.length]}30;color:${COLORS[t.length%COLORS.length]}">${esc(t)}</span>`).join('');
  document.getElementById('journal-expand-overlay').classList.add('open');
  document.getElementById('expanded-edit-btn').onclick = () => { closeJournalExpand(); openJournalModal(id); };
  document.getElementById('expanded-del-btn').onclick = () => { closeJournalExpand(); delJournal(id); };
};
window.closeJournalExpand = () => document.getElementById('journal-expand-overlay').classList.remove('open');

window.selectMood = (val, emoji) => {
  moodVal = val; moodEmoji = emoji;
  document.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
  document.querySelector(`.mood-option[data-mood="${val}"]`).classList.add('selected');
};

window.openJournalModal = (id = null) => {
  editJournalId = id; photoUrls = [];
  document.getElementById('photo-preview').innerHTML = '';
  document.getElementById('journal-photos').value = '';
  document.getElementById('jmodal-title').textContent = id ? '✏️ Edit Entry' : '📓 New Journal Entry';
  if (id) {
    const e = journals.find(j => j.id === id);
    if (e) {
      document.getElementById('journal-title').value   = e.title || '';
      document.getElementById('journal-content').value = e.content || '';
      document.getElementById('journal-tags').value    = (e.tags || []).join(', ');
      moodVal = e.mood || 3; moodEmoji = e.moodEmoji || '😐';
      if (e.photoUrls?.length) { photoUrls = [...e.photoUrls]; renderPreviews(); }
      document.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
      document.querySelector(`.mood-option[data-mood="${moodVal}"]`)?.classList.add('selected');
    }
  } else {
    document.getElementById('journal-title').value = '';
    document.getElementById('journal-content').value = '';
    document.getElementById('journal-tags').value = '';
    moodVal = 3; moodEmoji = '😐';
    document.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
  }
  document.getElementById('journal-modal').classList.add('open');
  setTimeout(() => document.getElementById('journal-content').focus(), 100);
};

document.getElementById('journal-photos').addEventListener('change', async e => {
  const files = Array.from(e.target.files);
  if (!files.length) return;
  if (photoUrls.length + files.length > 6) { toast('Max 6 photos','⚠️'); return; }
  toast(`Compressing ${files.length} photo(s)…`,'📷');
  for (const f of files) {
    try { photoUrls.push(await compressImage(f)); } catch { toast('Could not read '+f.name,'❌'); }
  }
  renderPreviews(); e.target.value = ''; toast('Photos ready!','📷');
});

function compressImage(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onerror = reject;
    reader.onload = ev => {
      const img = new Image();
      img.onerror = reject;
      img.onload = () => {
        const T = 200*1024; const c = document.createElement('canvas'); const ctx = c.getContext('2d');
        let m = 800, q = 0.8, r = '';
        for (let i = 0; i < 8; i++) {
          let w = img.width, h = img.height;
          if (w > h) { if (w > m) { h = Math.round(h*m/w); w = m; } }
          else { if (h > m) { w = Math.round(w*m/h); h = m; } }
          c.width = w; c.height = h; ctx.clearRect(0,0,w,h); ctx.drawImage(img,0,0,w,h);
          r = c.toDataURL('image/jpeg', q);
          if (r.length*.75 <= T) break;
          if (q > .3) q -= .15; else { m = Math.round(m*.7); q = .6; }
        }
        resolve(r);
      };
      img.src = ev.target.result;
    };
    reader.readAsDataURL(file);
  });
}

function renderPreviews() {
  document.getElementById('photo-preview').innerHTML = photoUrls.map((u,i) =>
    `<div style="position:relative;width:60px;height:60px;border-radius:8px;overflow:hidden;border:1px solid var(--border)">
      <img src="${u}" style="width:100%;height:100%;object-fit:cover">
      <button style="position:absolute;top:0;right:0;background:rgba(0,0,0,.7);color:white;border:none;cursor:pointer;width:20px;height:20px;font-size:.7rem" onclick="rmPhoto(${i})">×</button>
    </div>`
  ).join('');
}
window.rmPhoto = i => { photoUrls.splice(i,1); renderPreviews(); };

window.saveJournalEntry = async () => {
  console.log('saveJournalEntry called');
  const content = document.getElementById('journal-content').value.trim();
  if (!content) { toast('Write something first!','✏️'); return; }
  const data = {
    title: document.getElementById('journal-title').value.trim() || 'Untitled',
    content, mood: moodVal, moodEmoji,
    tags: document.getElementById('journal-tags').value.split(',').map(t=>t.trim()).filter(Boolean),
    photoUrls
  };
  try {
    if (editJournalId) {
      await updateDoc(doc(db,'users',currentUser.uid,'journals',editJournalId), data);
      const i = journals.findIndex(j => j.id === editJournalId);
      if (i !== -1) journals[i] = {...journals[i],...data};
      toast('Entry updated!','📓');
    } else {
      data.createdAt = Timestamp.now();
      const r = await addDoc(collection(db,'users',currentUser.uid,'journals'), data);
      journals.unshift({id:r.id,...data, createdAt:new Date()});
      toast('Entry saved! ✨','📓');
    }
    closeModal('journal-modal');
    await loadUserData();
    renderAll();
    editJournalId = null;
  } catch(e) { toast('Error: '+e.message,'❌'); }
};

window.delJournal = async id => {
  if (!confirm('Delete this entry?')) return;
  journals = journals.filter(j => j.id !== id);
  await deleteDoc(doc(db,'users',currentUser.uid,'journals',id));
  renderAll(); toast('Entry deleted','🗑️');
};

window.viewPhoto = url => {
  const d = document.createElement('div');
  d.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.93);display:flex;align-items:center;justify-content:center;z-index:9999';
  d.innerHTML = `<img src="${url}" style="max-width:92%;max-height:92%;border-radius:10px">
    <button style="position:absolute;top:20px;right:24px;background:none;border:none;color:white;font-size:2.2rem;cursor:pointer" onclick="this.parentElement.remove()">×</button>`;
  d.onclick = e => { if (e.target === d) d.remove(); };
  document.body.appendChild(d);
};

/* ══ TASKS ═══════════════════════════════════════════════════ */
window.openTaskModal = (id = null) => {
  editTaskId = id;
  document.getElementById('tmodal-title').textContent = id ? '✏️ Edit Task' : '✅ Add Task';
  if (id) {
    const t = tasks.find(t => t.id === id);
    if (t) {
      document.getElementById('task-text').value     = t.text;
      document.getElementById('task-note').value     = t.note || '';
      document.getElementById('task-priority').value = t.priority || 'med';
    }
  } else {
    document.getElementById('task-text').value = '';
    document.getElementById('task-note').value = '';
    document.getElementById('task-priority').value = 'med';
  }
  document.getElementById('task-modal').classList.add('open');
  setTimeout(() => document.getElementById('task-text').focus(), 100);
};

window.saveTask = async () => {
  console.log('saveTask called');
  const text = document.getElementById('task-text').value.trim();
  if (!text) { toast('Enter a task!','✏️'); return; }
  const data = { text, priority: document.getElementById('task-priority').value, note: document.getElementById('task-note').value.trim(), done: false };
  try {
    if (editTaskId) {
      await updateDoc(doc(db,'users',currentUser.uid,'tasks',editTaskId), data);
      const i = tasks.findIndex(t => t.id === editTaskId);
      if (i !== -1) tasks[i] = {...tasks[i],...data};
      toast('Task updated!','✅');
    } else {
      data.createdAt = Timestamp.now();
      const r = await addDoc(collection(db,'users',currentUser.uid,'tasks'), data);
      tasks.unshift({id:r.id,...data, createdAt:new Date()});
      toast('Task added!','✅');
    }
    closeModal('task-modal');
    await loadUserData();
    renderAll();
    editTaskId = null;
  } catch(e) { toast('Error: '+e.message,'❌'); }
};

window.toggleTask = async id => {
  const t = tasks.find(t => t.id === id); if (!t) return;
  t.done = !t.done;
  await updateDoc(doc(db,'users',currentUser.uid,'tasks',id), {done:t.done});
  if (t.done) toast('Task done! 🎉','✅');
  renderAll();
};

window.delTask = async id => {
  if (!confirm('Delete this task?')) return;
  tasks = tasks.filter(t => t.id !== id);
  await deleteDoc(doc(db,'users',currentUser.uid,'tasks',id));
  renderAll(); toast('Task removed','🗑️');
};

function renderTasksIn(container, list) {
  if (!container) return;
  if (!list.length) {
    container.innerHTML = `<div style="padding:14px;color:var(--muted);font-size:.8rem;text-align:center;font-style:italic">All clear here ✓</div>`;
    return;
  }
  container.innerHTML = list.map(t => `
    <div class="task-item ${t.done?'done':''}" draggable="true" ondragstart="dragStart(event,'${t.id}')" ondragover="dragOver(event)" ondrop="dropTask(event,'${t.priority}')">
      <div class="task-check ${t.done?'checked':''}" onclick="toggleTask('${t.id}')"></div>
      <div class="priority-dot p-${t.priority}"></div>
      <div class="task-text">${esc(t.text)}${t.note ? `<div style="font-size:.72rem;color:var(--muted);margin-top:2px">${esc(t.note)}</div>` : ''}</div>
      <button class="task-edit" onclick="openTaskModal('${t.id}')">✎</button>
      <button class="task-del" onclick="delTask('${t.id}')">✕</button>
    </div>`).join('');
}

window.dragStart = (e, id) => { draggedId = id; e.dataTransfer.effectAllowed = 'move'; };
window.dragOver  = e => e.preventDefault();
window.dropTask  = (e, priority) => {
  e.preventDefault();
  const t = tasks.find(t => t.id === draggedId);
  if (t) { t.priority = priority; updateDoc(doc(db,'users',currentUser.uid,'tasks',draggedId),{priority}); renderAll(); }
  draggedId = null;
};

/* ══ GOALS ═══════════════════════════════════════════════════ */
window.openGoalModal = (id = null) => {
  editGoalId = id;
  document.getElementById('gmodal-title').textContent = id ? '✏️ Edit Goal' : '🎯 New Goal';
  if (id) {
    const g = goals.find(g => g.id === id);
    if (g) {
      document.getElementById('goal-name').value     = g.name;
      document.getElementById('goal-target').value   = g.target || '';
      document.getElementById('goal-category').value = g.category || 'personal';
    }
  } else {
    document.getElementById('goal-name').value = '';
    document.getElementById('goal-target').value = '';
    document.getElementById('goal-category').value = 'personal';
  }
  document.getElementById('goal-modal').classList.add('open');
  setTimeout(() => document.getElementById('goal-name').focus(), 100);
};

window.saveGoal = async () => {
  const name = document.getElementById('goal-name').value.trim();
  if (!name) { toast('Name your goal!','🎯'); return; }
  const data = { name, category: document.getElementById('goal-category').value, target: document.getElementById('goal-target').value.trim() };
  try {
    if (editGoalId) {
      await updateDoc(doc(db,'users',currentUser.uid,'goals',editGoalId), data);
      toast('Goal updated!','🎯');
    } else {
      data.progress = 0; data.createdAt = Timestamp.now();
      await addDoc(collection(db,'users',currentUser.uid,'goals'), data);
      toast('Goal created! 🚀','🎯');
    }
    closeModal('goal-modal');
    await loadUserData();
    renderAll();
    editGoalId = null;
  } catch(e) {
    toast('Error: '+e.message,'❌');
    console.error('Error saving goal:', e);
  }
};

window.updGoal = async (id, delta) => {
  const g = goals.find(g => g.id === id); if (!g) return;
  g.progress = Math.max(0, Math.min(100, (g.progress||0) + delta));
  await updateDoc(doc(db,'users',currentUser.uid,'goals',id), {progress:g.progress});
  if (g.progress === 100) toast('🎉 Goal completed!','🏆');
  renderAll();
};

window.delGoal = async id => {
  if (!confirm('Delete this goal?')) return;
  goals = goals.filter(g => g.id !== id);
  await deleteDoc(doc(db,'users',currentUser.uid,'goals',id));
  renderAll(); toast('Goal removed','🗑️');
};

function renderGoalsIn(container, list, mini = false) {
  if (!container) return;
  if (!list.length) {
    container.innerHTML = `<div class="empty-state"><div class="empty-icon">🚀</div><div class="empty-text">No goals yet.</div><button class="btn btn-primary" onclick="openGoalModal()">Set first goal</button></div>`;
    return;
  }
  container.innerHTML = list.map((g,i) => `
    <div class="goal-item">
      <div class="goal-header">
        <div class="goal-name">${CAT_ICONS[g.category]||'⭐'} ${esc(g.name)}</div>
        <div style="display:flex;align-items:center;gap:8px">
          <div class="goal-pct">${g.progress||0}%</div>
          ${!mini ? `<button style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.75rem" onclick="openGoalModal('${g.id}')">✎</button>
                     <button style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.75rem" onclick="delGoal('${g.id}')">✕</button>` : ''}
        </div>
      </div>
      ${g.target ? `<div style="font-size:.72rem;color:var(--muted);margin-bottom:8px;font-family:'JetBrains Mono',monospace">Target: ${esc(g.target)}</div>` : ''}
      <div class="goal-progress-wrap"><div class="goal-progress-bar" style="width:${g.progress||0}%;background:${COLORS[i%COLORS.length]}"></div></div>
      ${!mini ? `<div class="goal-actions">
        <button class="btn-sm" onclick="updGoal('${g.id}',-10)">−10%</button>
        <button class="btn-sm" onclick="updGoal('${g.id}',10)">+10%</button>
        <button class="btn-sm" onclick="updGoal('${g.id}',25)">+25%</button>
        <button class="btn-sm" style="margin-left:auto" onclick="updGoal('${g.id}',${100-(g.progress||0)})">Complete ✓</button>
      </div>` : ''}
    </div>`).join('');
}

/* ══ INSIGHTS ════════════════════════════════════════════════ */
function calcStreak() {
  if (!journals.length) return 0;
  const uniq = [...new Set(journals.map(j => { const d=new Date(j.createdAt); return `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`; }))].sort().reverse();
  let streak = 0, cur = new Date();
  for (const s of uniq) {
    const [y,m,d] = s.split('-').map(Number);
    const entry = new Date(y,m,d);
    const diff = (new Date(cur.getFullYear(),cur.getMonth(),cur.getDate()) - entry) / 86400000;
    if (diff <= 1) { streak++; cur = entry; } else break;
  }
  return streak;
}

function renderInsights() {
  const streak = calcStreak();
  document.getElementById('insight-streak').textContent = streak;
  const cutoff = Date.now() - 7*86400000;
  const daily = {};
  journals.filter(j => new Date(j.createdAt) > cutoff).forEach(j => {
    const k = new Date(j.createdAt).toLocaleDateString();
    if (!daily[k]) daily[k] = [];
    daily[k].push(j.mood||3);
  });
  const td = document.getElementById('mood-trend-chart');
  const entries = Object.entries(daily);
  td.innerHTML = entries.length ? entries.map(([day,moods]) => {
    const avg = (moods.reduce((a,b)=>a+b,0)/moods.length).toFixed(1);
    return `<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
      <div style="width:70px;font-size:.68rem;color:var(--muted)">${day}</div>
      <div style="flex:1;background:var(--surface);border-radius:4px;height:20px;overflow:hidden">
        <div style="height:100%;background:linear-gradient(90deg,var(--rose),var(--amber),var(--accent),var(--green));width:${(avg/5)*100}%"></div>
      </div>
      <div style="width:28px;font-size:.8rem;font-weight:700;color:var(--accent)">${avg}</div>
    </div>`;
  }).join('') : `<div style="color:var(--muted);font-size:.8rem;padding:8px">No mood data yet.</div>`;

  const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  const mc = {5:'var(--green)',4:'var(--teal)',3:'var(--accent)',2:'var(--amber)',1:'var(--rose)'};
  document.getElementById('mood-chart').innerHTML = days.map((d,i) => {
    const ent = journals.filter(j => new Date(j.createdAt).getDay() === (i+1)%7);
    const avg = ent.length ? Math.round(ent.reduce((s,e)=>s+e.mood,0)/ent.length) : 0;
    return `<div class="mood-row">
      <div class="mood-day-label">${d}</div>
      <div class="mood-bar-wrap"><div class="mood-bar" style="width:${avg*20}%;background:${mc[avg]||'var(--muted)'}"></div></div>
      <div class="mood-val">${avg||'—'}</div>
    </div>`;
  }).join('');

  const done = tasks.filter(t=>t.done).length, total = tasks.length;
  const avgM = journals.length ? (journals.reduce((s,j)=>s+j.mood,0)/journals.length).toFixed(1) : '—';
  const top = [...goals].sort((a,b)=>b.progress-a.progress)[0];
  document.getElementById('activity-summary').innerHTML = `<div style="display:flex;flex-direction:column;gap:14px">
    ${[['📓','Journal Entries','Total: '+journals.length,journals.length,'var(--accent)'],
       ['✅','Task Completion',`${done} of ${total} done`,total?Math.round((done/total)*100)+'%':'0%','var(--green)'],
       ['😊','Average Mood','Based on journals',avgM+'/5','var(--lavender)']
      ].map(([icon,label,sub,val,color]) =>
      `<div class="mood-row" style="align-items:center">
        <span style="font-size:1.2rem;width:28px">${icon}</span>
        <div style="flex:1"><div style="font-size:.78rem;font-weight:600;margin-bottom:2px">${label}</div><div style="font-size:.68rem;color:var(--muted)">${sub}</div></div>
        <div style="font-size:1.1rem;font-weight:800;color:${color}">${val}</div>
      </div>`).join('')}
    ${top ? `<div class="mood-row" style="align-items:center">
      <span style="font-size:1.2rem;width:28px">🏆</span>
      <div style="flex:1"><div style="font-size:.78rem;font-weight:600;margin-bottom:2px">Top Goal</div><div style="font-size:.68rem;color:var(--muted)">${esc(top.name)}</div></div>
      <div style="font-size:1.1rem;font-weight:800;color:var(--amber)">${top.progress}%</div>
    </div>` : ''}
  </div>`;
}

/* ══ FILTER JOURNALS ═════════════════════════════════════════ */
window.filterJournals = () => {
  const q = document.getElementById('journal-search').value.toLowerCase().trim();
  if (!q) { renderJournals('journal-list'); return; }
  const orig = journals;
  journals = journals.filter(e =>
    e.title?.toLowerCase().includes(q) ||
    e.content?.toLowerCase().includes(q) ||
    (e.tags||[]).some(t => t.toLowerCase().includes(q))
  );
  renderJournals('journal-list');
  journals = orig;
};

/* ══ EXPORT ══════════════════════════════════════════════════ */
window.exportAsJSON = () => {
  const blob = new Blob([JSON.stringify({journals,tasks,goals,exportedAt:new Date().toISOString(),user:currentUser?.email},null,2)],{type:'application/json'});
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
  a.download = `lifevault-backup-${Date.now()}.json`; a.click();
  toast('Backup downloaded!','💾');
};

/* ══ RENDER ALL ══════════════════════════════════════════════ */
function renderAll() {
  const streak = calcStreak();
  document.getElementById('stat-entries').textContent = journals.length;
  document.getElementById('stat-tasks').textContent   = tasks.filter(t => t.done && new Date(t.createdAt) > new Date(Date.now()-7*86400000)).length;
  document.getElementById('stat-goals').textContent   = goals.filter(g => (g.progress||0) < 100).length;
  document.getElementById('stat-streak').textContent  = streak;
  document.getElementById('insight-streak').textContent = streak;
  if (journals.length) {
    const counts = {}; journals.forEach(j => { counts[j.mood] = (counts[j.mood]||0)+1; });
    const fav = Object.entries(counts).sort((a,b)=>b[1]-a[1])[0][0];
    document.getElementById('stat-mood').textContent = M_EMOJI[fav] || '😐';
  }

  // ── use new renderJournals(containerId, limit, isDash) ──
  renderJournals('dash-journal-list', 3, true);
  renderJournals('journal-list');

  const pending = tasks.filter(t => !t.done).slice(0,5);
  const dt = document.getElementById('dash-task-list');
  if (!pending.length) dt.innerHTML = `<div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">All tasks done!</div><button class="btn btn-primary" onclick="openTaskModal()">Add task</button></div>`;
  else renderTasksIn(dt, pending);

  renderTasksIn(document.getElementById('tasks-high'), tasks.filter(t=>t.priority==='high'));
  renderTasksIn(document.getElementById('tasks-med'), tasks.filter(t=>t.priority==='med'));
  renderTasksIn(document.getElementById('tasks-low'), tasks.filter(t=>t.priority==='low'));

  renderGoalsIn(document.getElementById('dash-goals-list'), goals.slice(0,3), true);
  renderGoalsIn(document.getElementById('goals-personal'), goals.filter(g=>g.category==='personal'));
  renderGoalsIn(document.getElementById('goals-work'), goals.filter(g=>g.category==='work'));
  renderGoalsIn(document.getElementById('goals-health'), goals.filter(g=>g.category==='health'));
  renderGoalsIn(document.getElementById('goals-finance'), goals.filter(g=>g.category==='finance'));
  renderGoalsIn(document.getElementById('goals-learning'), goals.filter(g=>g.category==='learning'));

  renderInsights();
}

window.onload = () => {
  navigateTo('dashboard');
};

window.openShareModal = (type) => {
  const modal = document.getElementById('share-modal');
  if (!modal) return;
  const listEl = document.getElementById('share-item-list');
  const titleEl = document.getElementById('share-modal-title');
  let items = [];
  let title = 'Share';
  let itemType = '';

  switch (type) {
    case 'journal':
      items = journals;
      title = 'Share a Journal Entry';
      itemType = 'journal';
      break;
    case 'task':
      items = tasks.filter(t => t.done);
      title = 'Share a Completed Task';
      itemType = 'task';
      break;
    case 'goal':
      items = goals.filter(g => g.progress === 100);
      title = 'Share a Completed Goal';
      itemType = 'goal';
      break;
  }

  if (titleEl) titleEl.textContent = title;
  if (listEl) {
    listEl.innerHTML = items.length ? items.map(item => `
      <div class="share-item" data-id="${item.id}" data-type="${itemType}">
        <div class="share-item-title">${esc(item.name || item.title || item.text)}</div>
        <div class="share-item-date">
          ${item.createdAt ? new Date(item.createdAt.toDate ? item.createdAt.toDate() : item.createdAt).toLocaleDateString() : ''}
        </div>
      </div>
    `).join('') : '<div class="empty-state">No items to share.</div>';

    // Add event listeners to the new items
    listEl.querySelectorAll('.share-item').forEach(item => {
      item.addEventListener('click', () => {
        // Visually mark as selected
        document.querySelectorAll('.share-item').forEach(i => i.classList.remove('selected'));
        item.classList.add('selected');
      });
    });
  }

  modal.classList.add('open');
};

window.shareSelectedItems = async () => {
  const selectedItem = document.querySelector('#share-item-list .share-item.selected');
  if (!selectedItem) {
    toast('Please select an item to share.', 'INFO');
    return;
  }

  const { type, id } = selectedItem.dataset;
  await window.shareItem(type, id);
}

window.shareItem = async (type, id) => {
  let item, text, photoUrls = [];
  switch (type) {
    case 'journal': 
      item = journals.find(i => i.id === id); 
      text = `Shared a journal entry: "${item.title}"`; 
      if (item.photoUrls) photoUrls = item.photoUrls;
      break;
    case 'task': 
      item = tasks.find(i => i.id === id); 
      text = `Completed a task: "${item.text}"`; 
      break;
    case 'goal': 
      item = goals.find(i => i.id === id); 
      text = `Achieved a goal: "${item.name}"`; 
      break;
  }

  if (!item) {
    toast('Item not found.', '❌');
    return;
  }

  const post = {
    type: type,
    text: text,
    photoUrls: photoUrls,
    authorId: currentUser.uid,
    authorName: currentUser.displayName,
    authorAvatar: currentUser.photoURL,
    likes: [],
    comments: [],
    createdAt: Timestamp.now()
  };

  try {
    await addDoc(collection(db, 'community'), post);
    toast('Shared to community!', '🚀');
    closeModal('share-modal');
    await loadCommunityFeed();
    renderCommunityFeed();
  } catch (e) {
    toast('Error sharing: ' + e.message, '❌');
    console.error(e);
  }
};

window.setComposerType = () => {
  // TODO: Implement composer type functionality
  toast('Composer type not implemented yet.', '🚧');
};

/* ══ COMMUNITY ═══════════════════════════════════════════════ */
let communityPosts = [];

window.postThought = async () => {
  const text = document.getElementById('composer-text').value.trim();
  if (!text) {
    toast('You have to write something first!', '🤔');
    return;
  }

  try {
    await addDoc(collection(db, 'community'), {
      text,
      type: 'thought',
      authorId: currentUser.uid,
      authorName: currentUser.displayName,
      authorAvatar: currentUser.photoURL,
      createdAt: Timestamp.now(),
      likes: [],
      comments: []
    });

    document.getElementById('composer-text').value = '';
    toast('Your thought has been shared!', '🚀');
    await loadCommunityFeed();
    renderCommunityFeed();
  } catch (e) {
    toast(`Error: ${e.message}`, '❌');
    console.error('Error posting thought:', e);
  }
};

async function loadCommunityFeed(filter = 'all') {
  const feedList = document.getElementById('feed-list');
  if (feedList) feedList.innerHTML = '<div class="loading-posts">Loading community posts…</div>';

  try {
    let q;
    const postsRef = collection(db, 'community');
    if (filter === 'all') {
      q = query(postsRef, orderBy('createdAt', 'desc'), limit(50));
    } else if (filter === 'mine') {
      q = query(postsRef, where('authorId', '==', currentUser.uid), orderBy('createdAt', 'desc'), limit(50));
    } else {
      q = query(postsRef, where('type', '==', filter), orderBy('createdAt', 'desc'), limit(50));
    }
    const snapshot = await getDocs(q);
    communityPosts = snapshot.docs.map(d => ({ id: d.id, ...d.data() }));
  } catch (e) {
    console.error("Error loading community feed:", e);
    toast('Error loading community feed.', '❌');
    communityPosts = [];
  }
}

function renderCommunityFeed() {
  const container = document.getElementById('feed-list');
  if (!container) return;

  if (!communityPosts.length) {
    container.innerHTML = '<div class="empty-state"><div class="empty-icon">💨</div><div class="empty-text">The community feed is quiet...</div><div class="empty-subtitle">Why not share something?</div></div>';
    return;
  }

  container.innerHTML = communityPosts.map(post => {
    const postDate = post.createdAt ? post.createdAt.toDate() : new Date();
    const timeAgo = formatTimeAgo(postDate);
    const isLiked = post.likes && post.likes.includes(currentUser.uid);
    const photosHTML = (post.photoUrls && post.photoUrls.length) 
      ? `<div class="post-photos">${post.photoUrls.map(url => `<img src="${url}" onclick="viewPhoto('${url}')">`).join('')}</div>`
      : '';

    return `
      <div class="feed-item" data-id="${post.id}">
        <img class="feed-item-avatar" src="${post.authorAvatar || 'https://via.placeholder.com/40'}" alt="${post.authorName}">
        <div class="feed-item-content">
          <div class="feed-item-header">
            <span class="feed-item-author">${esc(post.authorName)}</span>
            <span class="feed-item-timestamp">· ${timeAgo}</span>
          </div>
          <div class="feed-item-body">${esc(post.text)}</div>
          ${photosHTML}
          <div class="feed-item-actions">
            <button class="feed-action-btn ${isLiked ? 'liked' : ''}" onclick="toggleLike('${post.id}')">
              ❤️ <span class="likes-count">${post.likes ? post.likes.length : 0}</span>
            </button>
            <button class="feed-action-btn">💬 <span class="comment-count">${post.comments ? post.comments.length : 0}</span></button>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

window.filterFeed = async (filter, el) => {
  document.querySelectorAll('.feed-filter-btn').forEach(b => b.classList.remove('active'));
  if (el) el.classList.add('active');
  await loadCommunityFeed(filter);
  renderCommunityFeed();
};

window.toggleLike = async (postId) => {
  const postRef = doc(db, 'community', postId);
  const post = communityPosts.find(p => p.id === postId);
  if (!post) return;

  const isLiked = post.likes && post.likes.includes(currentUser.uid);
  
  // Optimistic update
  if (isLiked) {
    post.likes = post.likes.filter(uid => uid !== currentUser.uid);
  } else {
    if (!post.likes) post.likes = [];
    post.likes.push(currentUser.uid);
  }
  renderCommunityFeed();

  try {
    await updateDoc(postRef, {
      likes: isLiked ? arrayRemove(currentUser.uid) : arrayUnion(currentUser.uid)
    });
  } catch (e) {
    console.error("Error liking post:", e);
    // Revert optimistic update on error
    if (isLiked) {
      post.likes.push(currentUser.uid);
    } else {
      post.likes = post.likes.filter(uid => uid !== currentUser.uid);
    }
    renderCommunityFeed();
    toast('Error updating like.', '❌');
  }
}

function formatTimeAgo(date) {
  const seconds = Math.floor((new Date() - date) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + "y ago";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + "mo ago";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + "d ago";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + "h ago";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + "m ago";
  return Math.floor(seconds) + "s ago";
}

window.openEntryTypeModal = () => {
  const modal = document.getElementById('entry-type-modal');
  if(modal) modal.classList.add('open');
};