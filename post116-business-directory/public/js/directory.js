(() => {
  function qs(el, sel){ return el.querySelector(sel); }
  function qsa(el, sel){ return Array.from(el.querySelectorAll(sel)); }
  function h(tag, props={}, children=[]) {
    const e = document.createElement(tag);
    Object.assign(e, props);
    children.forEach(c => e.appendChild(typeof c === 'string' ? document.createTextNode(c) : c));
    return e;
  }
  function fetchJSON(url){ return fetch(url, {credentials:'same-origin'}).then(r => r.json()); }

  function renderCard(item){
    const wrap = h('div', {className:'p116bd-card'});
    const a = h('a', {className:'p116bd-card__link', href:item.permalink});
    a.appendChild(h('h3', {className:'p116bd-card__title', textContent:item.title}));
    if (item.city || item.phone) {
      const meta = h('div', {className:'p116bd-card__meta'});
      if (item.city) meta.appendChild(h('span', {className:'p116bd-city', textContent:item.city}));
      if (item.phone) meta.appendChild(h('span', {className:'p116bd-phone', textContent:item.phone}));
      a.appendChild(meta);
    }
    wrap.appendChild(a);
    return wrap;
  }

  function renderResults(root, res){
    const grid = qs(root, '.p116bd-grid');
    grid.innerHTML = '';
    // Group by first category name
    const groups = {};
    res.items.forEach(it => {
      const key = (it.categories && it.categories.length ? it.categories[0] : 'Uncategorized');
      (groups[key] ||= []).push(it);
    });
    Object.keys(groups).sort((a,b)=>a.localeCompare(b)).forEach(cat => {
      const h = document.createElement('h3'); h.textContent = cat; grid.appendChild(h);
      groups[cat].sort((a,b)=>a.title.localeCompare(b.title)).forEach(item => grid.appendChild(renderCard(item)));
    });
    const legal = qs(root, '.p116bd-legal');
    legal.textContent = qs(root, '.p116bd-results').dataset.legal;
    const pag = qs(root, '.p116bd-pagination');
    pag.innerHTML = '';
    if (res.pages > 1) {
      for(let i=1;i<=res.pages;i++){
        const b = h('button', {className:'p116bd-page' + (i===currentPage?' is-active':''), textContent:String(i)});
        b.addEventListener('click', () => { currentPage = i; doSearch(root); });
        pag.appendChild(b);
      }
    }
  }

  function buildURL(root){
    const q = qs(root, '.p116bd-q').value.trim();
    const cat = qs(root, '.p116bd-category').value;
    const per = root.dataset.perPage || 12;
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
