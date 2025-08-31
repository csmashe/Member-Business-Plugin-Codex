(() => {
  function qs(el, sel){ return el.querySelector(sel); }
  function qsa(el, sel){ return Array.from(el.querySelectorAll(sel)); }
  function h(tag, props={}, children=[]) {
    const e = document.createElement(tag);
    Object.assign(e, props);
    children.forEach(c => e.appendChild(typeof c === 'string' ? document.createTextNode(c) : c));
    return e;
  }
  async function fetchJSON(url){
    const res = await fetch(url, {credentials:'same-origin'});
    const ct = res.headers.get('content-type') || '';
    if (!res.ok) {
      let detail = '';
      try { detail = await res.text(); } catch(_) {}
      const msg = `HTTP ${res.status} ${res.statusText}${detail ? ' — ' + detail.slice(0,200) : ''}`;
      throw new Error(msg);
    }
    // No body
    if (res.status === 204) return {};
    if (ct.indexOf('application/json') !== -1) {
      try { return await res.json(); } catch(_) { return {}; }
    }
    // Fallback: try to parse as JSON, else return empty
    try {
      const text = await res.text();
      return text ? JSON.parse(text) : {};
    } catch(_) { return {}; }
  }

  function renderRow(item){
    const row = h('div', {className:'p116bd-row'});
    // Logo column
    const logoCol = h('div', {className:'p116bd-row__logo'});
    const logoLink = h('a', {className:'p116bd-logo-link', href:item.permalink, ariaLabel:`View ${item.title}`});
    if (item.logo) {
      const img = new Image();
      img.src = item.logo;
      img.alt = item.title + ' logo';
      logoLink.appendChild(img);
    } else {
      logoLink.appendChild(h('div', {className:'p116bd-logo--placeholder'}));
    }
    logoCol.appendChild(logoLink);
    row.appendChild(logoCol);

    // Info column
    const info = h('div', {className:'p116bd-row__info'});
    const title = h('h3', {className:'p116bd-row__title'});
    try { title.style.setProperty('font-size','21px','important'); } catch(_) {}
    const a = h('a', {href:item.permalink, textContent:item.title});
    title.appendChild(a);
    info.appendChild(title);

    const meta = h('div', {className:'p116bd-row__meta'});
    if (item.owner) meta.appendChild(h('span', {className:'p116bd-owner', textContent:item.owner}));
    if (item.phone) meta.appendChild(h('span', {className:'p116bd-phone', textContent:item.phone}));
    info.appendChild(meta);

    if (item.services) {
      info.appendChild(h('div', {className:'p116bd-row__services', textContent:item.services}));
    }
    if (item.flags && (item.flags.veteran_owned || item.flags.sons_owned || item.flags.auxiliary_owned)) {
      // Icons only, no text or boxes, must appear as the last row in the card
      const icons = h('div', {className:'p116bd-row__emblems'});
      if (item.flags.veteran_owned) {
        const i = h('img', {className:'p116bd-flag-icon', alt:'American Legion emblem', loading:'lazy'});
        i.src = (window.p116bdPluginUrl || '/wp-content/plugins/post116-business-directory/') + 'public/icons/TAL-emblem-full-detail-RGB.png';
        icons.appendChild(i);
      }
      if (item.flags.sons_owned) {
        const i = h('img', {className:'p116bd-flag-icon', alt:'Sons of The American Legion emblem', loading:'lazy'});
        i.src = (window.p116bdPluginUrl || '/wp-content/plugins/post116-business-directory/') + 'public/icons/SAL-Emblem.png';
        icons.appendChild(i);
      }
      if (item.flags.auxiliary_owned) {
        const i = h('img', {className:'p116bd-flag-icon', alt:'American Legion Auxiliary emblem', loading:'lazy'});
        i.src = (window.p116bdPluginUrl || '/wp-content/plugins/post116-business-directory/') + 'public/icons/Auxiliary-Emblem.png';
        icons.appendChild(i);
      }
      info.appendChild(icons);
    }
    row.appendChild(info);
    return row;
  }

  function renderResults(root, res, append){
    const grid = qs(root, '.p116bd-grid');
    if (!grid) return;
    const state = root._p116 || (root._p116 = {});
    state.pages = Number(res.pages || 1);
    const container = grid;
    if (!append) {
      container.innerHTML = '';
      state.catMap = new Map();
      state.catOrder = [];
    }

    // Clear any previous empty state
    const emptyEl = qs(root, '.p116bd-empty');
    if (emptyEl) emptyEl.remove();

    const items = Array.isArray(res.items) ? res.items : [];
    if (!append && items.length === 0) {
      // No results message; do not leave the grid blank
      const msg = h('p', { className: 'p116bd-empty', textContent: 'No results found. Try different terms or filters.' });
      container.innerHTML = '';
      container.appendChild(msg);
    }

    // Helpers for grouping
    const norm = (s) => (s || '').toString().trim().replace(/\s+/g,' ');
    const keyFor = (it, name, _idx) => {
      // If API provides a single-category row context, prefer it
      if (it.cat_slug) return it.cat_slug.toString().toLowerCase();
      return norm(name).toLowerCase();
    };

    // Build groups: key -> {label, items[]}
    const groups = {};
    items.forEach(it => {
      // When server provides per-category rows, group by that; otherwise fallback to all categories
      if (it.cat_label || it.cat_slug) {
        const key = (it.cat_slug || norm(it.cat_label)).toString().toLowerCase();
        const label = it.cat_label || it.categories?.[0] || 'Uncategorized';
        if (!groups[key]) groups[key] = { label: norm(label), items: [] };
        groups[key].items.push(it);
      } else {
        const names = Array.isArray(it.categories) && it.categories.length ? it.categories : ['Uncategorized'];
        names.forEach((name, idx) => {
          const key = keyFor(it, name, idx);
          if (!groups[key]) groups[key] = { label: norm(name), items: [] };
          groups[key].items.push(it);
        });
      }
    });

    const orderByLabel = (a, b) => a.label.localeCompare(b.label);
    const entries = Object.entries(groups).map(([key, val]) => ({ key, label: val.label, items: val.items }));
    entries.sort(orderByLabel);

    // Init state tracking
    state.catMap = state.catMap || new Map();
    state.catOrder = state.catOrder || [];
    state.catLabels = state.catLabels || new Map();

    // Create or reuse sections per category and append rows, keeping alpha order stable
    entries.forEach(({key, label, items}) => {
      let entry = state.catMap.get(key);
      if (!entry) {
        const section = h('section', {className:'p116bd-category'});
        const head = h('h2', {className:'p116bd-category__title', textContent:label});
        section.appendChild(head);
        const list = h('div', {className:'p116bd-list'});
        section.appendChild(list);
        // Determine insertion index by label
        const order = state.catOrder;
        // Store label for this key
        state.catLabels.set(key, label);
        let idx = order.findIndex(k => label.localeCompare(state.catLabels.get(k) || '') < 0);
        if (idx === -1) idx = order.length;
        order.splice(idx, 0, key);
        // Insert before the next section in order if it exists; otherwise before sentinel
        const nextKey = order[idx + 1];
        const nextEntry = nextKey ? state.catMap.get(nextKey) : null;
        const sentinel = state.sentinel;
        if (nextEntry && nextEntry.section && container.contains(nextEntry.section)) {
          container.insertBefore(section, nextEntry.section);
        } else if (sentinel && container.contains(sentinel)) {
          container.insertBefore(section, sentinel);
        } else {
          container.appendChild(section);
        }
        entry = { section, list, seen: new Set(), items: new Map() };
        state.catMap.set(key, entry);
      }
      const list = entry.list;
      const seen = entry.seen;
      const store = entry.items || (entry.items = new Map());
      // Merge items into store by id
      items.forEach(item => { if (!seen.has(item.id)) { seen.add(item.id); store.set(item.id, item); } });
      // Re-render list in alpha order using all collected items
      list.innerHTML = '';
      Array.from(store.values())
        .sort((a,b)=>a.title.localeCompare(b.title))
        .forEach(item => list.appendChild(renderRow(item)));
    });

    // After ensuring/creating sections, force DOM order to match alphabetical order
    if (state.catOrder && state.catOrder.length) {
      const sentinel = state.sentinel;
      const orderSorted = state.catOrder.slice().sort((ka, kb) => {
        const la = state.catLabels.get(ka) || '';
        const lb = state.catLabels.get(kb) || '';
        return la.localeCompare(lb);
      });
      state.catOrder = orderSorted;
      orderSorted.forEach(k => {
        const e = state.catMap.get(k);
        if (e && e.section) {
          // Move each section in order; append keeps sentinel last
          if (sentinel && container.contains(sentinel)) {
            container.insertBefore(e.section, sentinel);
          } else {
            container.appendChild(e.section);
          }
        }
      });
    }

    const legal = qs(root, '.p116bd-legal');
    const resultsEl = qs(root, '.p116bd-results');
    if (legal && resultsEl && resultsEl.dataset) {
      legal.textContent = resultsEl.dataset.legal || '';
    }

    // Ensure sentinel exists for infinite scroll
    let sent = state.sentinel;
    if (!sent) {
      sent = document.createElement('div');
      sent.className = 'p116bd-sentinel';
      sent.style.height = '1px';
      sent.style.width = '100%';
      container.appendChild(sent);
      state.sentinel = sent;
      setupObserver(root);
    } else if (!container.contains(sent)) {
      container.appendChild(sent);
    }

    // If user is already near bottom (fast scroll), kick the loader once
    maybeLoadMore(root);
  }

  function buildURL(root){
    const qEl = qs(root, '.p116bd-q');
    const q = qEl ? qEl.value.trim() : '';
    const catEl = qs(root, '.p116bd-category');
    const cat = catEl ? catEl.value : '';
    const perRaw = root && root.dataset ? root.dataset.perPage : undefined;
    let per = Number.parseInt(perRaw, 10);
    if (Number.isNaN(per) || per <= 0) per = 12;
    const flags = qsa(root, '.p116bd-flag:checked').map(e => e.value);
    const p = new URLSearchParams();
    if (q) p.set('q', q);
    if (cat) p.set('category', cat);
    if (flags.length) flags.forEach(f => p.append('flags[]', f));
    p.set('per_page', per);
    const state = root._p116 || (root._p116 = {page:1});
    p.set('page', state.page || 1);
    return (window.wpApiSettings?.root || '/wp-json/') + 'p116/v1/search?' + p.toString();
  }

  function doSearch(root, append){
    const state = root._p116 || (root._p116 = {page:1});
    if (state.loading) return;
    state.loading = true;
    // Blur the results section while loading, without clearing existing results
    const resultsWrap = qs(root, '.p116bd-results');
    if (resultsWrap) resultsWrap.classList.add('is-loading');
    const url = buildURL(root);
    fetchJSON(url).then(res => {
      renderResults(root, res, !!append);
    }).catch(() => {
      if (!append) qs(root, '.p116bd-grid').innerHTML = '<p class="p116bd-empty">Failed to load results.</p>';
    }).finally(()=>{
      state.loading = false;
      const results = qs(root, '.p116bd-results');
      if (results) results.classList.remove('is-loading');
    });
  }

  function setupObserver(root){
    const state = root._p116 || (root._p116 = {});
    if (state.observer) state.observer.disconnect();
    const sent = state.sentinel;
    if (!sent) return;
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const s = root._p116;
          if (!s || s.loading) return;
          if ((s.page || 1) >= (s.pages || 1)) return;
          s.page = (s.page || 1) + 1;
          doSearch(root, true);
        }
      });
    }, { rootMargin: '800px 0px' });
    io.observe(sent);
    state.observer = io;
  }

  // Backup loader: if sentinel is already near the viewport after render
  // (fast scroll), trigger a load without waiting for IO callback.
  function maybeLoadMore(root){
    const s = root._p116 || {};
    if (!s || s.loading) return;
    if ((s.page || 1) >= (s.pages || 1)) return;
    const sent = s.sentinel;
    if (!sent) return;
    const rect = sent.getBoundingClientRect();
    const vh = window.innerHeight || document.documentElement.clientHeight || 800;
    if (rect.top <= vh + 200) {
      s.page = (s.page || 1) + 1;
      doSearch(root, true);
    }
  }

  function attach(root){
    // reset state
    root._p116 = { page: 1, pages: 1, loading: false, sentinel: null, observer: null };
    const q = qs(root, '.p116bd-q');
    const cat = qs(root, '.p116bd-category');
    root.addEventListener('change', (e) => {
      if (e.target.matches('.p116bd-flag') || e.target === cat) {
        root._p116.page = 1; root._p116.pages = 1;
        doSearch(root, false);
      }
    });
    let t;
    // Autocomplete via datalist
    const dl = document.createElement('datalist');
    const dlId = 'p116bd-dl-' + Math.random().toString(36).slice(2);
    dl.id = dlId;
    q.setAttribute('list', dlId);
    q.parentNode.appendChild(dl);
    function doAC(){
      const val = q.value.trim();
      if (!val) { dl.innerHTML=''; return; }
      const base = (window.wpApiSettings?.root || '/wp-json/');
      fetchJSON(base + 'p116/v1/autocomplete?q=' + encodeURIComponent(val))
        .then(res => {
          dl.innerHTML = '';
          res.items.forEach(it => {
            const opt = document.createElement('option');
            opt.value = it.label;
            dl.appendChild(opt);
          });
        }).catch(()=>{});
    }
    q.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        doAC();
        root._p116.page = 1; root._p116.pages = 1;
        doSearch(root, false);
      }, 250);
    });

    // Fallback: listen to scroll/resize to handle very fast scrolling
    const onScroll = () => maybeLoadMore(root);
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    root._p116._onScroll = onScroll;
    doSearch(root, false);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.p116bd-directory').forEach(attach);
  });
})();
