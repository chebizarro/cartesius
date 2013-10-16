<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="ui.head">
		<xsl:call-template name="ui.jqwidgets" />
	</xsl:template>
	
	<xsl:template name="ui.easyui">
	
		<link rel="stylesheet" type="text/css" href="{$cs-scripts-lib}easyui/themes/default/easyui.css" />
		<link rel="stylesheet" type="text/css" href="{$cs-scripts-lib}easyui/themes/icon.css" />
		<link rel="stylesheet" type="text/css" href="{$cs-scripts-lib}easyui/themes/portal.css" />
		<link rel="stylesheet" type="text/css" href="{$cs-scripts-lib}easyui/demo/demo.css" />
	
		<script type="text/javascript" src="{$cs-scripts-lib}easyui/jquery.easyui.min.js"></script>
	
	</xsl:template>

	<xsl:template name="ui.jqwidgets">
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxknockout.js"></script>

		<link rel="stylesheet" href="{$cs-scripts-lib}jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
		<link rel="stylesheet" href="{$cs-scripts-lib}jqwidgets/jqwidgets/styles/jqx.bootstrap.css" type="text/css" />
		<link rel="stylesheet" href="{$cs-scripts-lib}jqwidgets/jqwidgets/styles/jqx.metro.css" type="text/css" />

		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxcore.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxcheckbox.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxprogressbar.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxbuttons.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxscrollbar.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxpanel.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxtree.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxsplitter.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxlistbox.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxexpander.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxnavigationbar.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxdata.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxmenu.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxgrid.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxgrid.selection.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxgrid.edit.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxscrollview.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxwindow.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxinput.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxtabs.js"></script>
		<!--<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/scripts/gettheme.js"></script>-->

		<script type="text/javascript" src="{$cs-scripts}ui.jqwidgets.js"></script>		

	</xsl:template>
	

</xsl:stylesheet>
