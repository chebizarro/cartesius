<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="geo.head">
		<xsl:call-template name="geo.openlayers" />
		
	</xsl:template>


	<xsl:template name="geo.mapquery">
		<link rel='stylesheet' type='text/css' href='{$cs-scripts-lib}mapquery/demo/style/style.css'/>

		<script src="http://maps.google.com/maps/api/js?v=3&amp;sensor=false" type="text/javascript"></script>
		<script src="{$cs-scripts-lib}openlayers/OpenLayers.js" type="text/javascript"></script>
		
		<script src="{$cs-scripts-lib}jquery/jquery.tmpl.js" type="text/javascript"></script>
		<script src="{$cs-scripts-lib}jquery/ui/ui/jquery.ui.widget.js" type="text/javascript"></script>
		
		<script src="{$cs-scripts-lib}mapquery/src/jquery.mapquery.core.js" type="text/javascript"></script>
		<script src="{$cs-scripts-lib}mapquery/src/jquery.mapquery.mqZoomButtons.js" type="text/javascript"></script>
	</xsl:template>

	<xsl:template name="geo.jquerygeo">
		<script src="{$cs-scripts-lib}jquerygeo/jquery.geo-1.0.0-b1.5.min.js" type="text/javascript"></script>
	</xsl:template>

	<xsl:template name="geo.openlayers">
		<script src="{$cs-scripts-lib}openlayers/OpenLayers.js" type="text/javascript"></script>
		<script src="{$cs-scripts}geo.openlayers.js" type="text/javascript"></script>
	</xsl:template>

</xsl:stylesheet>
