var style=document.createElement("style");function MultiselectDropdown(e){var t={search:!0,height:"15rem",placeholder:"Seleccionar...",txtSelected:"Seleccionados",txtAll:"Todo",txtRemove:"Remove",txtSearch:"Buscar",...e};function l(e,t){var l=document.createElement(e);return void 0!==t&&Object.keys(t).forEach(e=>{"class"===e?Array.isArray(t[e])?t[e].forEach(e=>""!==e?l.classList.add(e):0):""!==t[e]&&l.classList.add(t[e]):"style"===e?Object.keys(t[e]).forEach(d=>{l.style[d]=t[e][d]}):"text"===e?""===t[e]?l.innerHTML="&nbsp;":l.innerText=t[e]:l[e]=t[e]}),l}document.querySelectorAll("select[multiple]").forEach((e,d)=>{var o=l("div",{class:"multiselect-dropdown",style:{width:t.style?.width??e.clientWidth+"px",padding:t.style?.padding??""}});e.style.display="none",e.parentNode.insertBefore(o,e.nextSibling);var i=l("div",{class:"multiselect-dropdown-list-wrapper"}),r=l("div",{class:"multiselect-dropdown-list",style:{height:t.height}}),s=l("input",{class:["multiselect-dropdown-search"].concat([t.searchInput?.class??"form-control"]),style:{width:"100%",display:e.attributes["multiselect-search"]?.value==="true"?"block":"none"},placeholder:t.txtSearch});i.appendChild(s),o.appendChild(i),i.appendChild(r),e.loadOptions=()=>{if(r.innerHTML="",e.attributes["multiselect-select-all"]?.value=="true"){var d=l("div",{class:"multiselect-dropdown-all-selector"}),s=l("input",{type:"checkbox"});d.appendChild(s),d.appendChild(l("label",{text:t.txtAll})),d.addEventListener("click",()=>{d.classList.toggle("checked"),d.querySelector("input").checked=!d.querySelector("input").checked;var t=d.querySelector("input").checked;r.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)").forEach(e=>{"none"!==e.style.display&&(e.querySelector("input").checked=t,e.optEl.selected=t)}),e.dispatchEvent(new Event("change"))}),s.addEventListener("click",e=>{s.checked=!s.checked}),r.appendChild(d)}Array.from(e.options).map(t=>{var d=l("div",{class:t.selected?"checked":"",optEl:t}),o=l("input",{type:"checkbox",checked:t.selected});d.appendChild(o),d.appendChild(l("label",{text:t.text})),d.addEventListener("click",()=>{d.classList.toggle("checked"),d.querySelector("input").checked=!d.querySelector("input").checked,d.optEl.selected=!d.optEl.selected,e.dispatchEvent(new Event("change"))}),o.addEventListener("click",e=>{o.checked=!o.checked}),t.listitemEl=d,r.appendChild(d)}),o.listEl=i,o.refresh=()=>{o.querySelectorAll("span.optext, span.placeholder").forEach(e=>o.removeChild(e));var d=Array.from(e.selectedOptions);d.length>(e.attributes["multiselect-max-items"]?.value??5)?o.appendChild(l("span",{class:["optext","maxselected"],text:d.length+" "+t.txtSelected})):d.map(d=>{var i=l("span",{class:"optext",text:d.text,srcOption:d});e.attributes["multiselect-hide-x"]?.value!=="true"&&i.appendChild(l("span",{class:"optdel",text:"\uD83D\uDDD9",title:t.txtRemove,onclick(e){i.srcOption.listitemEl.dispatchEvent(new Event("click")),o.refresh(),e.stopPropagation()}})),o.appendChild(i)}),0==e.selectedOptions.length&&o.appendChild(l("span",{class:"placeholder",text:e.attributes.placeholder?.value??t.placeholder}))},o.refresh()},e.loadOptions(),s.addEventListener("input",()=>{r.querySelectorAll(":scope div:not(.multiselect-dropdown-all-selector)").forEach(e=>{var t=e.querySelector("label").innerText.toUpperCase();e.style.display=t.includes(s.value.toUpperCase())?"block":"none"})}),o.addEventListener("click",()=>{o.listEl.style.display="block",s.focus(),s.select()}),document.addEventListener("click",function(e){o.contains(e.target)||(i.style.display="none",o.refresh())})})}style.setAttribute("id","multiselect_dropdown_styles"),style.innerHTML=`
.multiselect-dropdown{
  display: inline-block;
  padding: 2px 5px 0px 5px;
  border-radius: 4px;
  border: solid 1px #ced4da;
  background-color: white;
  position: relative;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right .75rem center;
  background-size: 16px 12px;
  margin:2px 10px 0px 0px;
}
.multiselect-dropdown span.optext, .multiselect-dropdown span.placeholder{
  margin-right:0.5em; 
  margin-bottom:2px;
  padding:1px 0; 
  border-radius: 4px; 
  display:inline-block;
}
.multiselect-dropdown span.optext{
  background-color:lightgray;
  padding:1px 0.75em; 
}
.multiselect-dropdown span.optext .optdel {
  float: right;
  margin: 0 -6px 1px 5px;
  font-size: 0.7em;
  margin-top: 2px;
  cursor: pointer;
  color: #666;
}
.multiselect-dropdown span.optext .optdel:hover { color: #c66;}
.multiselect-dropdown span.placeholder{
  color:#ced4da;
}
.multiselect-dropdown-list-wrapper{
  box-shadow: gray 0 3px 8px;
  z-index: 100;
  padding:2px;
  border-radius: 4px;
  border: solid 1px #ced4da;
  display: none;
  margin: -1px;
  position: absolute;
  top:0;
  left: 0;
  right: 0;
  background: white;
}
.multiselect-dropdown-list-wrapper .multiselect-dropdown-search{
  margin-bottom:5px;
}
.multiselect-dropdown-list{
  padding:2px;
  height: 15rem;
  overflow-y:auto;
  overflow-x: hidden;
}
.multiselect-dropdown-list::-webkit-scrollbar {
  width: 6px;
}
.multiselect-dropdown-list::-webkit-scrollbar-thumb {
  background-color: #bec4ca;
  border-radius:3px;
}

.multiselect-dropdown-list div{
  padding: 5px;
}
.multiselect-dropdown-list input{
  height: 1.15em;
  width: 1.15em;
  margin-right: 0.35em;  
}
.multiselect-dropdown-list div.checked{
}
.multiselect-dropdown-list div:hover{
  background-color: #ced4da;
}
.multiselect-dropdown span.maxselected {width:75%;}
.multiselect-dropdown-all-selector {border-bottom:solid 1px #999;}
`,document.head.appendChild(style),window.addEventListener("load",()=>{MultiselectDropdown(window.MultiselectDropdownOptions)});
