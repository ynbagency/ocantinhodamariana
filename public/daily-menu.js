document.querySelectorAll('[data-catalogue-slot]').forEach(select=>{
 const key=select.dataset.catalogueSlot;
 const name=document.querySelector(`[data-slot-name="${key}"]`);
 const photo=document.querySelector(`[data-slot-photo="${key}"]`);
 select.addEventListener('change',()=>{const option=select.selectedOptions[0];if(select.value){name.value=option.dataset.name;photo.value=option.dataset.photo||'';}});
 name.addEventListener('input',()=>{select.value='';});
});
