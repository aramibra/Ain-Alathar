(function(){
  const el = document.getElementById('heritageMap');
  if(!el) return;

  const sites = (window.ATHAR_SITES || []).filter(s => Number.isFinite(+s.lat) && Number.isFinite(+s.lng));
  const opts = window.ATHAR_MAP_OPTIONS || {};
  const isPreview = !!window.ATHAR_PUBLIC_PREVIEW;
  const lang = window.ATHAR_LANG || document.documentElement.lang || 'ar';
  const tr = (ar,en)=>lang==='en'?en:ar;

  function loadCss(primary, fallback){
    if(document.querySelector('link[data-athar-leaflet]')) return;
    const l=document.createElement('link');
    l.rel='stylesheet'; l.href=primary; l.dataset.atharLeaflet='1';
    if(fallback) l.onerror=()=>{l.onerror=null;l.href=fallback;};
    document.head.appendChild(l);
  }
  function loadScript(primary, fallback, readyCheck){
    return new Promise((resolve,reject)=>{
      if(readyCheck && readyCheck()) return resolve();
      const add=(src,canFallback)=>{
        const s=document.createElement('script'); s.src=src; s.async=true;
        s.onload=resolve;
        s.onerror=()=>{s.remove(); if(canFallback && fallback) add(fallback,false); else reject(new Error('load'));};
        document.head.appendChild(s);
      };
      add(primary,true);
    });
  }
  function friendlyEmpty(msg){
    el.innerHTML='<div class="map-empty-state"><div><i class="ti ti-map-off"></i><b>'+tr('لا توجد بيانات خريطة متاحة','No map data available')+'</b><p>'+msg+'</p></div></div>';
  }
  function color(s){ return +s.risk>=70?'#dc3545':(+s.risk>=40?'#f0b429':'#21c17a'); }
  function escapeHtml(v){ return String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c])); }

  function init(){
    if(!sites.length){ friendlyEmpty(tr('لا توجد مواقع بإحداثيات صالحة للعرض.','No sites with valid coordinates are available.')); return; }

    const map = L.map(el,{
      scrollWheelZoom: opts.scrollWheelZoom ?? true,
      zoomControl: opts.zoomControl ?? true,
      dragging: opts.dragging ?? true,
      attributionControl: true
    });

    const tile = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
      maxZoom:19,
      attribution:'© OpenStreetMap',
      crossOrigin:true
    });
    tile.addTo(map);

    const markerLayer = L.layerGroup();
    const boundMarkers=[];
    sites.forEach(s=>{
      const marker=L.circleMarker([+s.lat,+s.lng],{
        radius:isPreview?7:9,
        color:'#fff',weight:3,
        fillColor:color(s),fillOpacity:1
      });
      const action=isPreview
        ? '<a href="login.php" class="btn btn-primary" style="margin-top:10px">'+tr('تسجيل الدخول للتفاصيل','Sign in for details')+'</a>'
        : '<a href="site.php?id='+encodeURIComponent(s.id)+'" class="btn btn-primary" style="margin-top:10px">'+tr('فتح التفاصيل','Open Details')+'</a>';
      marker.bindPopup('<div class="map-popup"><h4>'+escapeHtml(s.name)+'</h4><div>'+escapeHtml(s.city)+' — '+escapeHtml(s.type||'')+'</div><div>'+tr('مستوى الخطورة:','Risk Level:')+' <b>'+escapeHtml((+s.risk>=70?tr('مرتفع','High'):(+s.risk>=40?tr('متوسط','Medium'):tr('منخفض','Low'))))+'</b> ('+(+s.risk||0)+'%)</div>'+action+'</div>');
      markerLayer.addLayer(marker); boundMarkers.push(marker);
    });

    const heatPoints=sites.map(s=>[+s.lat,+s.lng,Math.max(.18,Math.min(1,(+s.risk||0)/100))]);
    let heatLayer=null;
    if(L.heatLayer){
      heatLayer=L.heatLayer(heatPoints,{
        radius:isPreview?32:45,
        blur:isPreview?24:32,
        maxZoom:12,
        minOpacity:.25,
        gradient:{0.2:'#21c17a',0.45:'#f0b429',0.7:'#ff7a3c',1:'#dc3545'}
      });
    }

    const group=L.featureGroup(boundMarkers);
    try{ map.fitBounds(group.getBounds().pad(isPreview?.45:.35),{maxZoom:isPreview?8:13}); }
    catch(e){ map.setView([28.2,36.2],6); }

    let mode=window.ATHAR_DEFAULT_MODE||'markers';
    function setMode(m){
      mode=m;
      markerLayer.removeFrom(map); if(heatLayer) heatLayer.removeFrom(map);
      if(m==='heat' && heatLayer) heatLayer.addTo(map);
      else if(m==='hybrid') { if(heatLayer) heatLayer.addTo(map); markerLayer.addTo(map); }
      else markerLayer.addTo(map);
      document.querySelectorAll('[data-map-mode]').forEach(b=>{
        b.classList.toggle('btn-primary',b.dataset.mapMode===m);
        b.classList.toggle('btn-light',b.dataset.mapMode!==m);
      });
      setTimeout(()=>map.invalidateSize(),80);
    }
    document.querySelectorAll('[data-map-mode]').forEach(b=>b.addEventListener('click',()=>setMode(b.dataset.mapMode)));
    setMode(mode);
    setTimeout(()=>map.invalidateSize(),250);
    window.addEventListener('resize',()=>map.invalidateSize());
  }

  loadCss(
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css'
  );
  loadScript(
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js',
    ()=>!!window.L
  )
  .then(()=>loadScript(
    'https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js',
    'https://cdn.jsdelivr.net/npm/leaflet.heat@0.2.0/dist/leaflet-heat.js',
    ()=>!!(window.L&&L.heatLayer)
  ).catch(()=>{}))
  .then(init)
  .catch(()=>friendlyEmpty(tr('تعذر تحميل الخريطة الآن. حاول تحديث الصفحة بعد التأكد من الاتصال بالإنترنت.','The map could not be loaded. Check your internet connection and refresh the page.')));
})();
