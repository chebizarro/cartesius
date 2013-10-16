<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" omit-xml-declaration="yes" indent="no"/>
	<xsl:template match ="/">
define(function(require) {
	return [
	<xsl:for-each select="/root/data/modules">
		require('/module/<xsl:value-of select="path"/>/module')<xsl:if test="position()!=last()">,</xsl:if>
	</xsl:for-each>
	];
});
	</xsl:template>

	<xsl:template match="text()" />
	
</xsl:stylesheet>
