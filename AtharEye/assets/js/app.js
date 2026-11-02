(function(){
  const qs=(s,c=document)=>c.querySelector(s), qsa=(s,c=document)=>[...c.querySelectorAll(s)];


  const publicMenuToggle=qs('[data-public-menu-toggle]'), publicNav=qs('[data-public-nav]');
  publicMenuToggle?.addEventListener('click',()=>{
    const isOpen=publicNav?.classList.toggle('open');
    publicMenuToggle.setAttribute('aria-expanded',isOpen?'true':'false');
    const glyph=publicMenuToggle.querySelector('.menu-glyph');
    if(glyph) glyph.textContent=isOpen?'×':'☰';
  });
  document.addEventListener('click',e=>{
    if(publicNav?.classList.contains('open') && !e.target.closest('[data-public-nav]') && !e.target.closest('[data-public-menu-toggle]')){
      publicNav.classList.remove('open');
      publicMenuToggle?.setAttribute('aria-expanded','false');
      const glyph=publicMenuToggle?.querySelector('.menu-glyph'); if(glyph) glyph.textContent='☰';
    }
  });
  qsa('[data-language-switch]').forEach(link=>link.addEventListener('click',()=>{
    try{sessionStorage.setItem('athar-lang-switch','1')}catch(_){}
  }));
  try{if(sessionStorage.getItem('athar-lang-switch')==='1'){sessionStorage.removeItem('athar-lang-switch');window.scrollTo(0,0)}}catch(_){}
  qsa('[data-password-toggle]').forEach(btn=>btn.addEventListener('click',()=>{
    const input=btn.closest('.password-field')?.querySelector('input'); if(!input)return;
    const show=input.type==='password'; input.type=show?'text':'password';
    const icon=btn.querySelector('i'); if(icon) icon.className=show?'ti ti-eye-off':'ti ti-eye';
    btn.setAttribute('aria-pressed',show?'true':'false');
  }));

  const sidebar=qs('.sidebar'), backdrop=qs('[data-sidebar-backdrop]');
  qsa('[data-sidebar-toggle]').forEach(b=>b.addEventListener('click',()=>{sidebar?.classList.toggle('open');backdrop?.classList.toggle('show')}));
  backdrop?.addEventListener('click',()=>{sidebar?.classList.remove('open');backdrop.classList.remove('show')});
  document.addEventListener('click',e=>{
    if(window.innerWidth<=1100 && sidebar?.classList.contains('open') && !e.target.closest('.sidebar') && !e.target.closest('[data-sidebar-toggle]')){sidebar.classList.remove('open');backdrop?.classList.remove('show')}
  });

  const userToggle=qs('[data-user-menu-toggle]'), userMenu=qs('[data-user-menu]');
  userToggle?.addEventListener('click',e=>{e.stopPropagation();userMenu?.classList.toggle('open');userToggle.setAttribute('aria-expanded',userMenu?.classList.contains('open')?'true':'false')});
  document.addEventListener('click',e=>{if(!e.target.closest('.user-menu-wrap')){userMenu?.classList.remove('open');userToggle?.setAttribute('aria-expanded','false')}});

  const search=qs('[data-global-search]');
  search?.addEventListener('keydown',e=>{if(e.key==='Enter'&&search.value.trim()) location.href='sites.php?q='+encodeURIComponent(search.value.trim())});

  qsa('[data-auto-dismiss]').forEach(n=>{
    n.querySelector('button')?.addEventListener('click',()=>n.remove());
    setTimeout(()=>{n.style.opacity='0';n.style.transform='translateY(-6px)';setTimeout(()=>n.remove(),250)},6500);
  });

  const modal=qs('#appModal'), modalContent=qs('#appModalContent');
  function openModal(html, wide=false){if(!modal||!modalContent)return;modalContent.innerHTML=html;modal.querySelector('.modal-card')?.classList.toggle('wide',wide);modal.classList.add('open');modal.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';setTimeout(()=>modalContent.querySelector('input,select,textarea')?.focus(),100)}
  function closeModal(){if(!modal)return;modal.classList.remove('open');modal.setAttribute('aria-hidden','true');document.body.style.overflow='';setTimeout(()=>{if(modalContent)modalContent.innerHTML=''},180)}
  qsa('[data-modal-close]').forEach(b=>b.addEventListener('click',closeModal));
  document.addEventListener('click',e=>{if(e.target.closest('[data-modal-close]')) closeModal();});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeModal();closeConfirm()}});
  window.AtharModal={open:openModal,close:closeModal};

  qsa('[data-modal-template]').forEach(btn=>btn.addEventListener('click',()=>{
    const tpl=qs(btn.dataset.modalTemplate); if(!tpl)return;
    openModal(tpl.innerHTML,btn.dataset.modalWide==='true');
  }));
  qsa('[data-edit-site]').forEach(btn=>btn.addEventListener('click',()=>{
    const tpl=qs('#siteFormTemplate'); if(!tpl)return;
    openModal(tpl.innerHTML,true);
    const form=qs('#siteModalForm',modalContent), data=JSON.parse(btn.dataset.site||'{}');
    if(!form)return;
    form.querySelector('[name="action"]').value='save';
    Object.entries(data).forEach(([k,v])=>{const el=form.querySelector(`[name="${k}"]`);if(el)el.value=v??''});
    qs('[data-modal-title]',modalContent).textContent=btn.dataset.title||'';
  }));
  qsa('[data-add-site]').forEach(btn=>btn.addEventListener('click',()=>{
    const tpl=qs('#siteFormTemplate');if(!tpl)return;openModal(tpl.innerHTML,true);
    const form=qs('#siteModalForm',modalContent);if(form){form.reset();form.querySelector('[name="id"]').value='';form.querySelector('[name="action"]').value='save'}
    qs('[data-modal-title]',modalContent).textContent=btn.dataset.title||'';
  }));
  qsa('[data-edit-user]').forEach(btn=>btn.addEventListener('click',()=>{
    const tpl=qs('#userFormTemplate');if(!tpl)return;openModal(tpl.innerHTML,false);
    const form=qs('#userModalForm',modalContent),data=JSON.parse(btn.dataset.user||'{}');if(!form)return;
    Object.entries(data).forEach(([k,v])=>{const el=form.querySelector(`[name="${k}"]`);if(el)el.value=v??''});
    qs('[data-modal-title]',modalContent).textContent=btn.dataset.title||'';
  }));
  qsa('[data-add-user]').forEach(btn=>btn.addEventListener('click',()=>{
    const tpl=qs('#userFormTemplate');if(!tpl)return;openModal(tpl.innerHTML,false);const form=qs('#userModalForm',modalContent);form?.reset();if(form)form.querySelector('[name="id"]').value='';qs('[data-modal-title]',modalContent).textContent=btn.dataset.title||'';
  }));

  const confirmDialog=qs('#confirmDialog'); let pendingForm=null;
  function closeConfirm(){confirmDialog?.classList.remove('open');confirmDialog?.setAttribute('aria-hidden','true');pendingForm=null;document.body.style.overflow=''}
  qsa('[data-confirm-cancel]').forEach(b=>b.addEventListener('click',closeConfirm));
  qs('.confirm-backdrop')?.addEventListener('click',closeConfirm);
  qs('[data-confirm-accept]')?.addEventListener('click',()=>{if(pendingForm){const f=pendingForm;pendingForm=null;f.submit()}closeConfirm()});
  qsa('form[data-confirm]').forEach(form=>form.addEventListener('submit',e=>{
    if(form.dataset.confirmed==='1')return;
    e.preventDefault();pendingForm=form;
    qs('[data-confirm-title]').textContent=form.dataset.confirmTitle||'';
    qs('[data-confirm-text]').textContent=form.dataset.confirmText||'';
    qs('[data-confirm-accept]').textContent=form.dataset.confirmButton||window.ATHAR_I18N?.delete||'Delete';
    confirmDialog?.classList.add('open');confirmDialog?.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';
  }));

  window.atharToast=function(message,type='success'){
    const stack=qs('#toastStack');if(!stack)return;const item=document.createElement('div');item.className='app-toast '+type;item.innerHTML=`<i class="ti ${type==='danger'?'ti-circle-x':'ti-circle-check'}"></i><span></span>`;item.querySelector('span').textContent=message;stack.appendChild(item);setTimeout(()=>{item.style.opacity='0';item.style.transform='translateY(8px)';setTimeout(()=>item.remove(),250)},3500)
  };

  qsa('[data-live-filter]').forEach(input=>input.addEventListener('input',()=>{
    const target=qs(input.dataset.liveFilter);if(!target)return;const q=input.value.trim().toLowerCase();qsa('[data-filter-row]',target).forEach(row=>row.hidden=!row.textContent.toLowerCase().includes(q));
  }));

  document.addEventListener('change',e=>{
    const input=e.target.closest('[data-file-input]');if(!input)return;
    const drop=input.closest('[data-file-dropzone]'),summary=drop?.querySelector('[data-file-summary]');
    const count=input.files?.length||0;
    if(drop)drop.classList.toggle('has-files',count>0);
    if(summary){
      const ar=document.documentElement.dir==='rtl';
      summary.textContent=count?(count===1?(ar?'تم اختيار صورة واحدة':'1 image selected'):(ar?`تم اختيار ${count} صور`:`${count} images selected`)):(ar?'يمكن رفع أكثر من صورة بصيغة JPG أو PNG.':'You may upload multiple JPG or PNG images.');
    }
  });
})();
