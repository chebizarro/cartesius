<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match ="/">
		<html>
			<body>
			<xsl:apply-templates />
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="/articles/article">
		<h2><xsl:value-of select="author" /></h2>
	
	</xsl:template>
</xsl:stylesheet>
