<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="4" encoding="UTF-8" indent="yes" />

	<xsl:param name="cs-scripts">/scripts/</xsl:param>
	<xsl:param name="cs-scripts-lib">/scripts/lib/</xsl:param>
	<xsl:param name="cs-styles">/style/</xsl:param>
	<xsl:param name="cs-images">/images/</xsl:param>
	<xsl:param name="cs-images-icons">/images/icons/</xsl:param>

	<xsl:template match="/">
		<div class='navigationBar' id='layersNavigationBar'>
			<div>
				<div>
					<div style='float: left;'>
						<img alt='Layers' src='{$cs-images-icons}layers.png' />
					</div>
					<div style='margin-left: 4px; float: left;'>Layers</div>
				</div>
			</div>
			<div>
				<div id="layersMenu" style="padding:2px;">
					<a href="" id="addLayer"><img style='float: left; margin-left:2px;' src='{$cs-images-icons}add.png' /></a>
					<a href="" id="deleteLater"><img style='float: left; margin-left:2px;' src='{$cs-images-icons}delete.png' /></a>
					<img style='float: left; margin-left:2px;' src='{$cs-images-icons}config.png' />
				</div>
				<div id="layersListbox">
					<xsl:attribute name='data-bind'>jqxListBox: <![CDATA[{checkboxes: showBoxes, selectedItemsCount: sltCount}]]></xsl:attribute>
				</div>
			</div>
		</div>
	</xsl:template>
	
</xsl:stylesheet>
