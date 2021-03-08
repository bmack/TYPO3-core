/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
var __importDefault=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};define(["require","exports","./Enum/Severity","jquery","./AjaxDataHandler","TYPO3/CMS/Core/Ajax/AjaxRequest","./InfoWindow","./Modal","./ModuleMenu","TYPO3/CMS/Backend/Notification","./Viewport"],(function(e,t,n,a,r,o,s,i,l,d,c){"use strict";a=__importDefault(a);class u{static getReturnUrl(){return encodeURIComponent(top.list_frame.document.location.pathname+top.list_frame.document.location.search)}static editRecord(e,t){let n="",r=a.default(this).data("pages-language-uid");r&&(n="&overrideVals[pages][sys_language_uid]="+r),c.ContentContainer.setUrl(top.TYPO3.settings.FormEngine.moduleUrl+"&edit["+e+"]["+t+"]=edit"+n+"&returnUrl="+u.getReturnUrl())}static viewRecord(){const e=a.default(this).data("preview-url");if(e){window.open(e,"newTYPO3frontendWindow").focus()}}static openInfoPopUp(e,t){s.showItem(e,t)}static mountAsTreeRoot(e,t){if("pages"===e){const e=new CustomEvent("typo3:pagetree:mountPoint",{detail:{pageId:t}});top.document.dispatchEvent(e)}}static newPageWizard(e,t){c.ContentContainer.setUrl(top.TYPO3.settings.NewRecord.moduleUrl+"&id="+t+"&pagesOnly=1&returnUrl="+u.getReturnUrl())}static newContentWizard(){const e=a.default(this);let t=e.data("new-wizard-url");t&&(t+="&returnUrl="+u.getReturnUrl(),i.advanced({title:e.data("title"),type:i.types.ajax,size:i.sizes.medium,content:t,severity:n.SeverityEnum.notice}))}static newRecord(e,t){c.ContentContainer.setUrl(top.TYPO3.settings.FormEngine.moduleUrl+"&edit["+e+"][-"+t+"]=new&returnUrl="+u.getReturnUrl())}static openHistoryPopUp(e,t){c.ContentContainer.setUrl(top.TYPO3.settings.RecordHistory.moduleUrl+"&element="+e+":"+t+"&returnUrl="+u.getReturnUrl())}static openListModule(e,t){const n="pages"===e?t:a.default(this).data("page-uid");l.App.showModule("web_list","id="+n)}static pagesSort(){const e=a.default(this).data("pages-sort-url");e&&c.ContentContainer.setUrl(e)}static pagesNewMultiple(){const e=a.default(this).data("pages-new-multiple-url");e&&c.ContentContainer.setUrl(e)}static disableRecord(e,t){const n=a.default(this).data("disable-field")||"hidden";c.ContentContainer.setUrl(top.TYPO3.settings.RecordCommit.moduleUrl+"&data["+e+"]["+t+"]["+n+"]=1&redirect="+u.getReturnUrl()).done(()=>{u.refreshPageTree()})}static enableRecord(e,t){const n=a.default(this).data("disable-field")||"hidden";c.ContentContainer.setUrl(top.TYPO3.settings.RecordCommit.moduleUrl+"&data["+e+"]["+t+"]["+n+"]=0&redirect="+u.getReturnUrl()).done(()=>{u.refreshPageTree()})}static showInMenus(e,t){c.ContentContainer.setUrl(top.TYPO3.settings.RecordCommit.moduleUrl+"&data["+e+"]["+t+"][nav_hide]=0&redirect="+u.getReturnUrl()).done(()=>{u.refreshPageTree()})}static hideInMenus(e,t){c.ContentContainer.setUrl(top.TYPO3.settings.RecordCommit.moduleUrl+"&data["+e+"]["+t+"][nav_hide]=1&redirect="+u.getReturnUrl()).done(()=>{u.refreshPageTree()})}static deleteRecord(e,t){const o=a.default(this);i.confirm(o.data("title"),o.data("message"),n.SeverityEnum.warning,[{text:a.default(this).data("button-close-text")||TYPO3.lang["button.cancel"]||"Cancel",active:!0,btnClass:"btn-default",name:"cancel"},{text:a.default(this).data("button-ok-text")||TYPO3.lang["button.delete"]||"Delete",btnClass:"btn-warning",name:"delete"}]).on("button.clicked",n=>{if("delete"===n.target.getAttribute("name")){const n={component:"contextmenu",action:"delete",table:e,uid:t};r.process("cmd["+e+"]["+t+"][delete]=1",n).then(()=>{"pages"===e&&(t===top.fsMod.recentIds.web&&top.document.dispatchEvent(new CustomEvent("typo3:pagetree:selectFirstNode")),u.refreshPageTree())})}i.dismiss()})}static copy(e,t){const n=TYPO3.settings.ajaxUrls.contextmenu_clipboard+"&CB[el]["+e+"%7C"+t+"]=1&CB[setCopyMode]=1";new o(n).get().finally(()=>{u.triggerRefresh(c.ContentContainer.get().location.href)})}static clipboardRelease(e,t){const n=TYPO3.settings.ajaxUrls.contextmenu_clipboard+"&CB[el]["+e+"%7C"+t+"]=0";new o(n).get().finally(()=>{u.triggerRefresh(c.ContentContainer.get().location.href)})}static cut(e,t){const n=TYPO3.settings.ajaxUrls.contextmenu_clipboard+"&CB[el]["+e+"%7C"+t+"]=1&CB[setCopyMode]=0";new o(n).get().finally(()=>{u.triggerRefresh(c.ContentContainer.get().location.href)})}static triggerRefresh(e){e.includes("record%2Fedit")||c.ContentContainer.refresh()}static clearCache(e,t){new o(TYPO3.settings.ajaxUrls.web_list_clearpagecache).withQueryArguments({id:t}).get({cache:"no-cache"}).then(async e=>{const t=await e.resolve();!0===t.success?d.success(t.title,t.message,1):d.error(t.title,t.message,1)},()=>{d.error("Clearing page caches went wrong on the server side.")})}static pasteAfter(e,t){u.pasteInto.bind(a.default(this))(e,-t)}static pasteInto(e,t){const r=a.default(this),o=()=>{const n="&CB[paste]="+e+"%7C"+t+"&CB[pad]=normal&redirect="+u.getReturnUrl();c.ContentContainer.setUrl(top.TYPO3.settings.RecordCommit.moduleUrl+n).done(()=>{"pages"===e&&u.refreshPageTree()})};if(!r.data("title"))return void o();i.confirm(r.data("title"),r.data("message"),n.SeverityEnum.warning,[{text:a.default(this).data("button-close-text")||TYPO3.lang["button.cancel"]||"Cancel",active:!0,btnClass:"btn-default",name:"cancel"},{text:a.default(this).data("button-ok-text")||TYPO3.lang["button.ok"]||"OK",btnClass:"btn-warning",name:"ok"}]).on("button.clicked",e=>{"ok"===e.target.getAttribute("name")&&o(),i.dismiss()})}static refreshPageTree(){top.document.dispatchEvent(new CustomEvent("typo3:pagetree:refresh"))}}return u}));