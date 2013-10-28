<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="4" encoding="UTF-8" indent="yes" />

	<xsl:param name="cs-scripts">/scripts/</xsl:param>
	<xsl:param name="cs-scripts-lib">/scripts/lib/</xsl:param>
	<xsl:param name="cs-styles">/style/</xsl:param>
	<xsl:param name="cs-images">/images/</xsl:param>
	<xsl:param name="cs-images-icons">/images/icons/</xsl:param>

	<xsl:template match="/">
		<div class='navigationBar'>
			<div>
				<div>
					<div style='float: left;'>
						<img alt='People' src='{$cs-images-icons}people.png' />
					</div>
					<div style='margin-left: 4px; float: left;'>People</div>
				</div>
			</div>
			<div>
				<div id="peopleMenu" style="padding:2px;">
					<a href="" id="addPerson"><img style='float: left; margin-left:2px;' src='{$cs-images-icons}add.png' /></a>
					<a href="" id="deletePerson"><img style='float: left; margin-left:2px;' src='{$cs-images-icons}delete.png' /></a>
					<img style='float: left; margin-left:2px;' src='{$cs-images-icons}config.png' />
					<img style='float: left; margin-left:2px;' src='{$cs-images-icons}location.png' />
				</div>
				<div id="peopleListbox">
				</div>
			</div>
		</div>
	</xsl:template>
	
</xsl:stylesheet>
