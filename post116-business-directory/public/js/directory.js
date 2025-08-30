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
    if (item.logo) {
      const img = new Image();
      img.src = item.logo;
      img.alt = item.title + ' logo';
      logoCol.appendChild(img);
    } else {
      logoCol.appendChild(h('div', {className:'p116bd-logo--placeholder'}));
    }
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
    if (!append) container.innerHTML = '';

    // Group by first category name and append
    const groups = {};
    (res.items || []).forEach(it => {
      const key = (it.categories && it.categories.length ? it.categories[0] : 'Uncategorized');
      (groups[key] ||= []).push(it);
    });
    Object.keys(groups).sort((a,b)=>a.localeCompare(b)).forEach(cat => {
      const section = h('section', {className:'p116bd-category'});
      const head = h('h2', {className:'p116bd-category__title', textContent:cat});
      section.appendChild(head);
      const list = h('div', {className:'p116bd-list'});
      groups[cat].sort((a,b)=>a.title.localeCompare(b.title)).forEach(item => list.appendChild(renderRow(item)));
      section.appendChild(list);
      container.appendChild(section);
    });

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
    const url = buildURL(root);
    fetchJSON(url).then(res => {
      renderResults(root, res, !!append);
    }).catch(() => {
      if (!append) qs(root, '.p116bd-grid').innerHTML = '<p>Failed to load results.</p>';
    }).finally(()=>{ state.loading = false; });
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
    }, { rootMargin: '200px 0px' });
    io.observe(sent);
    state.observer = io;
  }

  function attach(root){
    // reset state
    root._p116 = { page: 1, pages: 1, loading: false, sentinel: null, observer: null };
    const q = qs(root, '.p116bd-q');
    const cat = qs(root, '.p116bd-category');
    root.addEventListener('change', (e) => {
      if (e.target.matches('.p116bd-flag') || e.target === cat) {
        root._p116.page = 1; root._p116.pages = 1;
        qs(root, '.p116bd-grid').innerHTML = '';
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
        qs(root, '.p116bd-grid').innerHTML = '';
        doSearch(root, false);
      }, 250);
    });
    doSearch(root, false);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.p116bd-directory').forEach(attach);
  });
})();
