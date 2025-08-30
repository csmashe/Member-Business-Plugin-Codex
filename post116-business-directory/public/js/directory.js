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
    row.appendChild(info);
    return row;
  }

  function renderResults(root, res){
    const grid = qs(root, '.p116bd-grid');
    if (!grid) return;
    grid.innerHTML = '';
    // Group by first category name
    const groups = {};
    res.items.forEach(it => {
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
      grid.appendChild(section);
    });
    const legal = qs(root, '.p116bd-legal');
    const resultsEl = qs(root, '.p116bd-results');
    if (legal && resultsEl && resultsEl.dataset) {
      legal.textContent = resultsEl.dataset.legal || '';
    }
    const pag = qs(root, '.p116bd-pagination');
    if (pag) {
      pag.innerHTML = '';
      if (res.pages > 1) {
        for(let i=1;i<=res.pages;i++){
          const b = h('button', {className:'p116bd-page' + (i===currentPage?' is-active':''), textContent:String(i)});
          b.addEventListener('click', () => { currentPage = i; doSearch(root); });
          pag.appendChild(b);
        }
      }
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
    p.set('page', currentPage);
    return (window.wpApiSettings?.root || '/wp-json/') + 'p116/v1/search?' + p.toString();
  }

  function doSearch(root){
    const url = buildURL(root);
    fetchJSON(url).then(res => renderResults(root, res)).catch(() => {
      qs(root, '.p116bd-grid').innerHTML = '<p>Failed to load results.</p>';
    });
  }

  let currentPage = 1;
  function attach(root){
    const q = qs(root, '.p116bd-q');
    const cat = qs(root, '.p116bd-category');
    root.addEventListener('change', (e) => {
      if (e.target.matches('.p116bd-flag') || e.target === cat) { currentPage = 1; doSearch(root); }
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
    q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { doAC(); currentPage = 1; doSearch(root); }, 250); });
    doSearch(root);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.p116bd-directory').forEach(attach);
  });
})();
