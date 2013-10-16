<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="framework.head">
		<xsl:call-template name="framework.jquery" />
	</xsl:template>

	<xsl:template name="framework.jquery">
		<script type="text/javascript" src="{$cs-scripts-lib}jquery/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}knockout/knockout-2.3.0.js"></script>
		<script type="text/javascript" src="{$cs-scripts-lib}jqwidgets/jqwidgets/jqxknockout.js"></script>

		<script type="text/javascript" src="{$cs-scripts}framework.jquery.js"></script>

	</xsl:template>

</xsl:stylesheet>
