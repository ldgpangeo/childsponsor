/*

Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.

For licensing, see LICENSE.html or http://ckeditor.com/license

*/



(function(){function a(d,e){var f;try{f=d.getSelection().getRanges()[0];}catch(g){return null;}f.shrink(CKEDITOR.SHRINK_TEXT);return f.getCommonAncestor().getAscendant(e,true);};var b={a:'lower-alpha',A:'upper-alpha',i:'lower-roman',I:'upper-roman',1:'decimal',disc:'disc',circle:'circle',square:'square'};function c(d,e){if(e=='bulletedListStyle')return{title:d.lang.list.bulletedTitle,minWidth:300,minHeight:50,contents:[{id:'info',accessKey:'I',elements:[{type:'select',label:d.lang.list.type,id:'type',style:'width: 150px; margin: auto;',items:[[d.lang.list.notset,''],[d.lang.list.circle,'circle'],[d.lang.list.disc,'disc'],[d.lang.list.square,'square']],setup:function(g){var h=g.getStyle('list-style-type')||b[g.getAttribute('type')]||g.getAttribute('type')||'';this.setValue(h);},commit:function(g){var h=this.getValue();if(h)g.setStyle('list-style-type',h);else g.removeStyle('list-style-type');}}]}],onShow:function(){var g=this.getParentEditor(),h=a(g,'ul');h&&this.setupContent(h);},onOk:function(){var g=this.getParentEditor(),h=a(g,'ul');h&&this.commitContent(h);}};else if(e=='numberedListStyle'){var f=[[d.lang.list.notset,''],[d.lang.list.lowerRoman,'lower-roman'],[d.lang.list.upperRoman,'upper-roman'],[d.lang.list.lowerAlpha,'lower-alpha'],[d.lang.list.upperAlpha,'upper-alpha'],[d.lang.list.decimal,'decimal']];if(!CKEDITOR.env.ie||CKEDITOR.env.version>7)f.concat([[d.lang.list.armenian,'armenian'],[d.lang.list.decimalLeadingZero,'decimal-leading-zero'],[d.lang.list.georgian,'georgian'],[d.lang.list.lowerGreek,'lower-greek']]);return{title:d.lang.list.numberedTitle,minWidth:300,minHeight:50,contents:[{id:'info',accessKey:'I',elements:[{type:'hbox',widths:['25%','75%'],children:[{label:d.lang.list.start,type:'text',id:'start',validate:CKEDITOR.dialog.validate.integer(d.lang.list.validateStartNumber),setup:function(g){var h=g.getAttribute('start')||1;h&&this.setValue(h);},commit:function(g){g.setAttribute('start',this.getValue());}},{type:'select',label:d.lang.list.type,id:'type',style:'width: 100%;',items:f,setup:function(g){var h=g.getStyle('list-style-type')||b[g.getAttribute('type')]||g.getAttribute('type')||'';this.setValue(h);},commit:function(g){var h=this.getValue();if(h)g.setStyle('list-style-type',h);else g.removeStyle('list-style-type');}}]}]}],onShow:function(){var g=this.getParentEditor(),h=a(g,'ol');h&&this.setupContent(h);},onOk:function(){var g=this.getParentEditor(),h=a(g,'ol');h&&this.commitContent(h);}};}};CKEDITOR.dialog.add('numberedListStyle',function(d){return c(d,'numberedListStyle');

});CKEDITOR.dialog.add('bulletedListStyle',function(d){return c(d,'bulletedListStyle');});})();

